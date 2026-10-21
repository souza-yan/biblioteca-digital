<?php

use App\Enums\SchoolRegion;
use App\Enums\SchoolType;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\QueryException;

it('persists schools with enum casts', function () {
    $school = School::factory()->create([
        'type' => SchoolType::Emef,
        'region' => SchoolRegion::North,
    ]);

    expect($school->type)->toBe(SchoolType::Emef)
        ->and($school->region)->toBe(SchoolRegion::North);
});

it('rejects a duplicate school name', function () {
    School::factory()->create(['name' => 'EMEF Teste Única']);

    expect(fn () => School::factory()->create(['name' => 'EMEF Teste Única']))
        ->toThrow(QueryException::class);
});

it('returns schools regardless of the legacy status value', function () {
    $activeSchool = School::factory()->create();
    $legacySchool = School::factory()->create();
    $legacySchool->forceFill(['is_active' => false])->save();

    expect(School::query()->get()->modelKeys())
        ->toContain($activeSchool->getKey())
        ->toContain($legacySchool->getKey());
});

it('allows a teacher to belong to two schools', function () {
    $teacher = User::factory()->teacher()->create();
    $schools = School::factory()->count(2)->create();

    $teacher->schools()->attach($schools->modelKeys());

    expect($teacher->schools()->count())->toBe(2);
});

it('recognizes only active school memberships', function () {
    $teacher = User::factory()->teacher()->create();
    $activeSchool = School::factory()->create();
    $inactiveMembershipSchool = School::factory()->create();
    $teacher->schools()->attach($activeSchool->getKey(), ['is_active' => true]);
    $teacher->schools()->attach($inactiveMembershipSchool->getKey(), ['is_active' => false]);

    expect($teacher->belongsToSchool($activeSchool))->toBeTrue()
        ->and($teacher->belongsToSchool($inactiveMembershipSchool))->toBeFalse();
});

it('returns true for an exclusive active school membership', function () {
    $teacher = User::factory()->teacher()->create();
    $school = School::factory()->create();
    $teacher->schools()->attach($school->getKey(), ['is_active' => true]);

    expect($teacher->isExclusiveTo($school))->toBeTrue();
});

it('returns false when the teacher belongs to more than one school', function () {
    $teacher = User::factory()->teacher()->create();
    $school = School::factory()->count(2)->create();
    $teacher->schools()->attach($school->modelKeys());

    expect($teacher->isExclusiveTo($school->first()))->toBeFalse();
});

it('returns false for an inactive exclusive membership', function () {
    $teacher = User::factory()->teacher()->create();
    $school = School::factory()->create();
    $teacher->schools()->attach($school->getKey(), ['is_active' => false]);

    expect($teacher->isExclusiveTo($school))->toBeFalse();
});

it('returns the active school for staff and null when the user has no school', function () {
    $staff = User::factory()->staff()->create();
    $admin = User::factory()->admin()->create();
    $school = School::factory()->create();
    $staff->schools()->attach($school->getKey(), ['is_active' => true]);

    expect($staff->school()?->is($school))->toBeTrue()
        ->and($admin->school())->toBeNull();
});

it('returns no school context when staff has anything other than one active school', function () {
    $staff = User::factory()->staff()->create();
    $school = School::factory()->count(2)->create();
    $staff->schools()->attach($school->modelKeys(), ['is_active' => true]);

    expect($staff->school())->toBeNull()
        ->and($staff->hasExactlyOneActiveSchool())->toBeFalse();

    $staff->schools()->updateExistingPivot($school->last()->getKey(), ['is_active' => false]);

    expect($staff->school()?->is($school->first()))->toBeTrue()
        ->and($staff->hasExactlyOneActiveSchool())->toBeTrue();
});

it('rejects duplicate school memberships', function () {
    $teacher = User::factory()->teacher()->create();
    $school = School::factory()->create();
    $teacher->schools()->attach($school->getKey());

    expect(fn () => $teacher->schools()->attach($school->getKey()))
        ->toThrow(QueryException::class);
});

it('keeps school memberships for every registered school', function () {
    $teacher = User::factory()->teacher()->create();
    $school = School::factory()->create();
    $teacher->schools()->attach($school->getKey());

    $this->assertDatabaseHas('school_user', [
        'school_id' => $school->getKey(),
        'user_id' => $teacher->getKey(),
    ]);
});
