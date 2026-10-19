<?php

use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('serializes concurrent school assignments for one staff user', function () {
    if (! function_exists('pcntl_fork')) {
        $this->markTestSkipped('The pcntl extension is required for this concurrency test.');
    }

    $connectionName = 'school_assignment_concurrency';
    config()->set("database.connections.{$connectionName}", config('database.connections.mysql'));
    DB::purge($connectionName);

    $admin = User::factory()->admin()->make([
        'email' => 'concurrency-admin-'.uniqid().'@example.test',
    ]);
    $admin->setConnection($connectionName);
    $admin->save();

    $staff = User::factory()->staff()->make([
        'email' => 'concurrency-staff-'.uniqid().'@example.test',
    ]);
    $staff->setConnection($connectionName);
    $staff->save();

    $schools = collect(range(1, 2))->map(function (int $index) use ($connectionName): School {
        $school = School::factory()->make(['name' => 'Concurrency school '.uniqid().$index]);
        $school->setConnection($connectionName);
        $school->save();

        return $school;
    });

    $workers = [];
    $channels = [];

    try {
        foreach ($schools as $school) {
            [$parentSocket, $childSocket] = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
            $processId = pcntl_fork();

            if ($processId === -1) {
                throw new RuntimeException('Could not fork a school assignment worker.');
            }

            if ($processId === 0) {
                fclose($parentSocket);
                fread($childSocket, 1);
                fclose($childSocket);

                $workerCode = <<<'PHP'
                require getcwd().'/vendor/autoload.php';
                $app = require getcwd().'/bootstrap/app.php';
                $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

                try {
                    app(\App\Actions\User\UpdateUser::class)->handle(
                        \App\Models\User::query()->findOrFail((int) getenv('SCHOOL_ASSIGNMENT_ADMIN_ID')),
                        \App\Models\User::query()->findOrFail((int) getenv('SCHOOL_ASSIGNMENT_STAFF_ID')),
                        [
                            'role' => 'staff',
                            'school_id' => (int) getenv('SCHOOL_ASSIGNMENT_SCHOOL_ID'),
                        ],
                    );
                } catch (\Throwable) {
                    exit(1);
                }

                exit(0);
                PHP;
                $environment = getenv();
                $environment['APP_ENV'] = 'testing';
                $environment['DB_CONNECTION'] = 'mysql';
                $environment['DB_DATABASE'] = (string) config('database.connections.mysql.database');
                $environment['DB_URL'] = '';
                $environment['SCHOOL_ASSIGNMENT_ADMIN_ID'] = (string) $admin->getKey();
                $environment['SCHOOL_ASSIGNMENT_STAFF_ID'] = (string) $staff->getKey();
                $environment['SCHOOL_ASSIGNMENT_SCHOOL_ID'] = (string) $school->getKey();

                pcntl_exec(PHP_BINARY, ['-r', $workerCode], $environment);

                exit(2);
            }

            fclose($childSocket);
            $workers[] = $processId;
            $channels[] = $parentSocket;
        }

        foreach ($channels as $channel) {
            fwrite($channel, '1');
            fclose($channel);
        }

        foreach ($workers as $worker) {
            pcntl_waitpid($worker, $status);
            expect(pcntl_wifexited($status))->toBeTrue()
                ->and(pcntl_wexitstatus($status))->toBe(0);
        }

        $activeSchoolIds = DB::connection($connectionName)
            ->table('school_user')
            ->where('user_id', $staff->getKey())
            ->where('is_active', true)
            ->pluck('school_id');

        expect($activeSchoolIds)->toHaveCount(1)
            ->and($schools->pluck('id'))->toContain($activeSchoolIds->first());
    } finally {
        foreach ($channels as $channel) {
            if (is_resource($channel)) {
                fclose($channel);
            }
        }

        foreach ($workers as $worker) {
            pcntl_waitpid($worker, $status, WNOHANG);
        }

        $connection = DB::connection($connectionName);
        $connection->table('school_user')->whereIn('school_id', $schools->pluck('id'))->delete();
        $connection->table('activity_logs')->where('user_id', $admin->getKey())->delete();
        $connection->table('users')->whereIn('id', [$admin->getKey(), $staff->getKey()])->delete();
        $connection->table('schools')->whereIn('id', $schools->pluck('id'))->delete();
        DB::purge($connectionName);
    }
});
