<?php

namespace Caronae\Http\Controllers\API\V1;


use Caronae\Http\Resources\MessageResource;
use Caronae\Models\Message;
use Caronae\Models\Ride;
use Caronae\Models\User;
use Caronae\Notifications\RideMessageReceived;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function should_return_chat_messages_ordered_by_date()
    {
        $user = factory(User::class)->create();
        $ride = factory(Ride::class)->create();
        $ride->users()->attach($user, ['status' => 'driver']);

        $messages = [
            Message::create([
                'ride_id' => $ride->id,
                'user_id' => $user->id,
                'body' => 'Olá mundo!',
                'created_at' => '1990-01-01 01:00:00',
            ]),
            Message::create([
                'ride_id' => $ride->id,
                'user_id' => $user->id,
                'body' => 'Hello world!',
                'created_at' => '1990-01-01 00:00:00',
            ]),
        ];

        $response = $this->jsonAs($user,'GET', 'api/v1/rides/' . $ride->id . '/messages', []);
        $response->assertStatus(200);
        $response->assertExactJson([
            'messages' => [
                [
                    'id' => $messages[1]->id,
                    'body' => 'Hello world!',
                    'user' => $user->toArray(),
                    'date' => '1990-01-01 00:00:00',
                ],
                [
                    'id' => $messages[0]->id,
                    'body' => 'Olá mundo!',
                    'user' => $user->toArray(),
                    'date' => '1990-01-01 01:00:00',
                ]
            ]
        ]);
    }

    /** @test */
    public function should_store_chat_message_with_encrypted_body()
    {
        $user = factory(User::class)->create();
        $ride = factory(Ride::class)->create();
        $ride->users()->attach($user, ['status' => 'accepted']);

        $response = $this->jsonAs($user,'POST', 'api/v1/rides/' . $ride->id . '/messages', [
            'message' => 'Hello world!'
        ]);

        $response->assertStatus(201);
        $createdMessage = Message::find($response->json()['id']);
        $this->assertEquals($ride->id, $createdMessage->ride_id);
        $this->assertEquals($user->id, $createdMessage->user_id);
        $this->assertEquals('Hello world!', decrypt($createdMessage->getAttributeValue('body')));
    }

    /** @test */
    public function should_send_notification_to_riders_except_sender()
    {
        $user = factory(User::class)->create();
        $ride = factory(Ride::class)->create();
        $ride->users()->attach($user, ['status' => 'accepted']);

        $user2 = factory(User::class)->create();
        $ride->users()->attach($user2, ['status' => 'accepted']);
        $user3 = factory(User::class)->create();
        $ride->users()->attach($user3, ['status' => 'driver']);

        $this->expectsNotification($user2, RideMessageReceived::class);
        $this->expectsNotification($user3, RideMessageReceived::class);

        $response = $this->jsonAs($user,'POST', 'api/v1/rides/' . $ride->id . '/messages', [
            'message' => 'Hello world!'
        ]);

        $response->assertStatus(201);
    }
}
