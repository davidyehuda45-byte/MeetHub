<?php

namespace Tests\Unit;

use App\Models\Meeting;
use App\Models\User;
use App\Services\JitsiJwtService;
use Tests\TestCase;

class JitsiJwtServiceTest extends TestCase
{
    protected JitsiJwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JitsiJwtService();
    }

    public function test_generates_valid_jwt_for_host_with_moderator_true(): void
    {
        $host = User::factory()->make([
            'id' => 1,
            'name' => 'Budi Host',
            'email' => 'budi@example.com',
            'role' => 'host',
        ]);

        $meeting = new Meeting([
            'id' => 10,
            'host_id' => 1,
            'room_code' => 'mh-abcd-1234',
            'jitsi_room_name' => 'meethub_mhabcd1234_test',
        ]);

        $token = $this->jwtService->generateToken($meeting, $host);
        $this->assertNotEmpty($token);

        $decoded = $this->jwtService->decodeToken($token);
        $this->assertNotNull($decoded);
        $this->assertEquals(config('jitsi.app_id'), $decoded->iss);
        $this->assertEquals(config('jitsi.app_id'), $decoded->aud);
        $this->assertEquals('meethub_mhabcd1234_test', $decoded->room);
        $this->assertEquals('Budi Host', $decoded->context->user->name);
        $this->assertTrue($decoded->context->user->moderator);
    }

    public function test_generates_valid_jwt_for_guest_with_moderator_false(): void
    {
        $meeting = new Meeting([
            'id' => 10,
            'host_id' => 1,
            'room_code' => 'mh-abcd-1234',
            'jitsi_room_name' => 'meethub_mhabcd1234_test',
        ]);

        $token = $this->jwtService->generateToken($meeting, null, 'Tamu Joni');
        $this->assertNotEmpty($token);

        $decoded = $this->jwtService->decodeToken($token);
        $this->assertNotNull($decoded);
        $this->assertEquals('Tamu Joni', $decoded->context->user->name);
        $this->assertFalse($decoded->context->user->moderator);
    }
}

