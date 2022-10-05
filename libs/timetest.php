<?php

declare(strict_types=1);

if (defined('PHPUNIT_TESTSUITE')) {
    trait TestTime
    {
        private $currentTime = 989884800;

        protected function getTime()
        {
            return $this->currentTime;
        }

        public function setTime(int $Time)
        {
            $this->currentTime = $Time;
        }

        public function GetTimerInterval(string $Ident)
        {
            return parent::GetTimerInterval($Ident);
        }
    }
} else {
    trait TestTime
    {
        protected function getTime()
        {
            return time();
        }
    }
}