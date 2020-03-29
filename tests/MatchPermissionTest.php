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

    public function testNoRules(): void
    {
        $rules = collect([]);

        $this->assertFalse($this->user->matchPermission($rules, $this->permission));
    }

    public function testCorrectRule(): void
    {
        $rules = collect([
            ['company', 'new'],
            ['user', 'edit'],
        ]);

        $this->assertTrue($this->user->matchPermission($rules, $this->permission));
    }

    public function testWildcardRule(): void
    {
        $rules = collect([
            ['company', 'new'],
            ['user', '*'],
        ]);

        $this->assertTrue($this->user->matchPermission($rules, $this->permission));
    }

    public function testForbiddenRule(): void
    {
        $rules = collect([
            ['foo', '*'],
        ]);

        $this->assertFalse($this->user->matchPermission($rules, $this->permission));
    }
}
