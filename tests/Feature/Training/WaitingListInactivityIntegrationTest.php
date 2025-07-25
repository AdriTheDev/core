<?php

namespace Tests\Feature\Training;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Notifications\Training\RemovedFromWaitingListInactiveAccount;
use App\Notifications\Training\RemovedFromWaitingListNonHomeMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListInactivityIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->actingAs($this->privacc);
    }

    #[Test]
    public function it_should_react_to_real_account_altered_event_for_inactivity()
    {
        $this->markTestSkipped('The event is not fired as it is currently disabled.');

        $account = Account::factory()->create(['inactive' => false]);
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create();
        $waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($waitingList->accounts->contains($account));

        // saving the inactive account will trigger the AccountAltered event.
        $account->refresh();
        $account->inactive = true;
        $account->save();

        $this->assertFalse($waitingList->refresh()->accounts->contains($account));
        Notification::assertSentToTimes($account, RemovedFromWaitingListInactiveAccount::class, 1);
    }

    #[Test]
    public function it_should_react_to_real_account_altered_event_for_inactivity_not_on_list()
    {
        $this->markTestSkipped('The event is not fired as it is currently disabled.');

        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create();

        $this->assertFalse($waitingList->accounts->contains($account));

        $account->inactive = true;
        $account->save();

        $this->assertFalse($waitingList->fresh()->accounts->contains($account));
        Notification::assertNothingSentTo($account, RemovedFromWaitingListInactiveAccount::class);
    }

    #[Test]
    #[Group('test1')]
    public function it_should_react_to_real_account_altered_event_for_state_changed()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->refresh();

        /** @var WaitingList $waitingList */
        $waitingList = WaitingList::factory()->create();
        $waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($waitingList->includesAccount($account));

        $account->addState(State::findByCode('REGION'));

        $this->assertFalse($waitingList->fresh()->includesAccount($account));
        Notification::assertSentToTimes($account, RemovedFromWaitingListNonHomeMember::class, 1);
    }

    #[Test]
    public function it_should_react_to_real_account_altered_event_for_mship_state_not_on_list()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = WaitingList::factory()->create();

        $this->assertFalse($waitingList->includesAccount($account));

        $account->updateDivision('EUD', 'EUR');
        $account->refresh();

        $this->assertFalse($waitingList->includesAccount($account));
        Notification::assertNothingSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }
}
