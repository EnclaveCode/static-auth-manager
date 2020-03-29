<?php

namespace Enclave\StaticAuthManager\Test;

class MatchPermissionTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->permission = [
            'user',
            'edit',
        ];
        $this->user = User::create(['email' => 'test@user.com']);
    }

    /** @test */
    public function no_rules(): void
    {
        $rules = collect([]);

        $this->assertFalse($this->user->matchPermission($rules, $this->permission));
    }

    /** @test */
    public function correct_rule(): void
    {
        $rules = collect([
            ['company', 'new'],
            ['user', 'edit'],
        ]);

        $this->assertTrue($this->user->matchPermission($rules, $this->permission));
    }

    /** @test */
    public function wildcard_rule(): void
    {
        $rules = collect([
            ['company', 'new'],
            ['user', '*'],
        ]);

        $this->assertTrue($this->user->matchPermission($rules, $this->permission));
    }

    /** @test */
    public function forbidden_rule(): void
    {
        $rules = collect([
            ['foo', '*'],
        ]);

        $this->assertFalse($this->user->matchPermission($rules, $this->permission));
    }
}
