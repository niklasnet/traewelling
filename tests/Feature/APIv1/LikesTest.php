<?php

namespace Tests\Feature\APIv1;

use App\Models\Status;
use App\Models\TrainCheckin;
use App\Models\User;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\ApiTestCase;

class LikesTest extends ApiTestCase
{

    use RefreshDatabase;

    public function testCreateShowAndDestroyLike(): void {
        $user      = User::factory()->create();
        $userToken = $user->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $checkin   = TrainCheckin::factory()->create();
        $status    = $checkin->status;

        $this->assertDatabaseMissing('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);

        $response = $this->postJson(
            uri:     '/api/v1/status/' . $status->id . '/like',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertCreated();

        $this->assertDatabaseHas('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);

        //Should fail: Already liked
        $response = $this->postJson(
            uri:     '/api/v1/status/' . $status->id . '/like',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertStatus(409);

        $response = $this->get(
            uri:     '/api/v1/status/' . $status->id . '/likes',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();
        $response->assertJsonStructure([
                                           'data' => [
                                               '*' => [
                                                   'id',
                                                   'displayName',
                                                   'username',
                                                   'profilePicture',
                                                   'trainDistance',
                                                   'trainDuration',
                                                   'trainSpeed',
                                                   'points',
                                                   'mastodonUrl',
                                                   'privateProfile',
                                                   'preventIndex',
                                                   'userInvisibleToMe',
                                                   'muted',
                                                   'following',
                                                   'followPending',
                                               ]
                                           ]
                                       ]);
        $this->assertCount(1, $response->json('data'));

        $response = $this->deleteJson(
            uri:     '/api/v1/status/' . $status->id . '/like',
            headers: ['Authorization' => 'Bearer ' . $userToken]
        );
        $response->assertOk();

        $this->assertDatabaseMissing('likes', [
            'user_id'   => $user->id,
            'status_id' => $status->id,
        ]);
    }

    public function testDestroyShouldFailIfWrongStatusIdGiven() {
        $response = $this->deleteJson(
            uri:     '/api/v1/like/999999999', //some high id which should not exist in our test-data
            headers: ['Authorization' => 'Bearer ' . $this->getTokenForTestUser()]
        );
        $response->assertNotFound();
    }

    public function testCannotLikeIfStatusAuthorHasDisabledLikes(): void {
        $bob      = User::factory()->create();
        $bobToken = $bob->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $status     = Status::factory()->create();
        $alice      = $status->user;
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $alice->update(["likes_enabled" => false]);

        $response = $this->postJson(
            uri:     '/api/v1/status/' . $status->id . '/like',
            headers: ['Authorization' => 'Bearer ' . $bobToken]
        );
        $response->assertStatus(403);

        $this->assertSeeNumberOfLikes($status, $bobToken, 0);
        Auth::forgetUser();
        $this->assertSeeNumberOfLikes($status, $aliceToken, 0);
    }

    public function testBobDoesntSeeLikesIfBobHasDisabledLikes(): void {
        $checkin    = TrainCheckin::factory()->create();
        $alice      = $checkin->status->user;
        $aliceToken = $alice->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;

        $response = $this->postJson(
            uri:     '/api/v1/status/' . $checkin->status->id . '/like',
            headers: ['Authorization' => 'Bearer ' . $aliceToken]
        );
        $response->assertCreated();
        $this->assertSeeNumberOfLikes($checkin->status, $aliceToken, 1);

        Auth::forgetUser();

        $bob      = User::factory(["likes_enabled" => false])->create();
        $bobToken = $bob->createToken('token', array_keys(AuthServiceProvider::$scopes))->accessToken;
        $this->assertSeeNumberOfLikes($checkin->status, $bobToken, 0);
    }

    private function assertSeeNumberOfLikes(Status $status, string $token, int $expectedLikeCount): void {
        $response = $this->get(
            uri:     '/api/v1/status/' . $status->id . '/likes',
            headers: ['Authorization' => 'Bearer ' . $token]
        );
        $response->assertOk();
        $this->assertCount($expectedLikeCount, $response->json('data'));
    }
}
