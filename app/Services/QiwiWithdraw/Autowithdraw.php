<?php

use App\AutowithdrawTypes;
use App\QiwiWallet;
use App\QiwiWalletSettings;
use App\Services\Withdraw;

define("AUTOWITHDRAW_EVERY_X_MINUTES", 1);
define("AUTOWITHDRAW_AFTER_BALANCE_UPDATE", 2);
define("AUTOWITHDRAW_MANUALLY", 3);

class Autowithdraw {
    /**
     * @var QiwiWallet
     */
    private $wallet;
    /**
     * @var QiwiWalletSettings
     */
    private $settings;
    /**
     * @var AutowithdrawTypes
     */
    private $autoWithdrawType;

    private $login;


    function __construct($walletLogin) {
        $this->login = $walletLogin;
        $this->init();
    }

    private function init() {
        $this->wallet = QiwiWallet::where("login", $this->login)->first();
        $this->settings = QiwiWalletSettings::find($this->wallet->id);
        $this->autoWithdrawType = AutowithdrawTypes::find($this->settings->autoWithdrawal_type_id);
    }

    public function autoWithdraw($autowithdrawMode) {
        if (!$this->guards($autowithdrawMode)) return;

        switch ($autowithdrawMode) {
            case AUTOWITHDRAW_EVERY_X_MINUTES:
                $this->everyXMin();
                break;

            default:
                break;
        }
    }

    private function everyXMin() {

    }

    private function guards($mode) {
        if (!$this->isModeRight($mode)) return false;
        if (!$this->settings->isAutoWidthdrawalActive()) return false;

        return true;
    }

    private function isModeRight($mode) {
        switch ($mode) {
            case AUTOWITHDRAW_EVERY_X_MINUTES:
                if ($this->autoWithdrawType->isEveryXMinutes() && $this->settings->isTimeToWithdraw()) {
                    return true;
                }
                break;

            case AUTOWITHDRAW_AFTER_BALANCE_UPDATE:
                if ($this->autoWithdrawType->isAfterBalanceUpdate()) return true;
                break;

            case AUTOWITHDRAW_MANUALLY:
                if ($this->autoWithdrawType->isManually()) return true;
                break;
        }

        return false;
    }
}