<?php

namespace Enclave\StaticAuthManager\Test;

class MatchRuleToPermissionTest extends TestCase
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
    public function direct_rule(): void
    {
        $rule = [
            'user',
            'edit',
        ];

        $this->assertTrue($this->user->matchRuleToPermission($rule, $this->permission));
    }

    /** @test */
    public function wildcard_rule(): void
    {
        $rule = [
            'user',
            '*',
        ];

        $this->assertTrue($this->user->matchRuleToPermission($rule, $this->permission));
    }

    /** @test */
    public function wildcard_at_end_rule(): void
    {
        $rule = [
            'user',
            'edit',
            '*',
        ];

        $this->assertTrue($this->user->matchRuleToPermission($rule, $this->permission));
    }

    /** @test */
    public function wrong_rule(): void
    {
        $rule = [
            'user',
            'blabla',
        ];

        $this->assertFalse($this->user->matchRuleToPermission($rule, $this->permission));
    }

    /** @test */
    public function more_precise_rule(): void
    {
        $rule = [
            'user',
            'edit',
            'self',
        ];

        $this->assertFalse($this->user->matchRuleToPermission($rule, $this->permission));
    }


    /** @test */
    public function short_rule(): void
    {
        $rule = [
            'user',
        ];

        $this->assertFalse($this->user->matchRuleToPermission($rule, $this->permission));
    }
}
