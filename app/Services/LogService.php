<?php


namespace App\Services;


use App\Models\MembershipLog;
use App\Models\SubscriptionLog;
use DateInterval;
use DateTime;

class LogService
{
  /**
   * @var array
   *  membership log function
   */
  public function membershipLog($user,$customer,$amount)
  {
    MembershipLog::create([
      'customer_id' => $user,
      'membership_id' => $customer,
      'amount' => $amount,

    ]);
  }

  /**
   * @var array
   * subscription log function
   */
  public function subscriptionLog($user,$customer,$amount)
  {
    $currentDate = new DateTime();
    $currentDate->add(new DateInterval('P1M'));
    $expires_at = $currentDate->format('Y-m-d');

    SubscriptionLog::create([
      'customer_id' => $user,
      'membership_id' => $customer,
      'amount' => $amount,
      'expires_at' => $expires_at,
    ]);
  }
}
