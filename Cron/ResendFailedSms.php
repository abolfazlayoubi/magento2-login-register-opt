<?php


namespace Magesoft\Otp\Cron;
use Magesoft\Otp\Model\OtpManager;
use Magesoft\Otp\Model\Method\Logger;

class ResendFailedSms
{

    /**
     * @var OtpManager
     */
    protected $otpManager;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * ResendFailedSms constructor.
     * @param OtpManager $otpManager
     * @param Logger $logger
     */
    public function __construct(
        OtpManager $otpManager,
        Logger $logger
    )
    {
        $this->otpManager=$otpManager;
        $this->logger=$logger;
    }

    /**
     *
     * @return void
     */
    public function execute() {
        try{
            $items=$this->otpManager->getFailedRows();
            $this->logger->info("Cron Run:".$items->getSize()." Items Fetch");
            foreach ($items as $item){
                $this->otpManager
                    ->sendSms($item->getId(),$item->getMobileNumber(),$item->getCode());
            }
        }catch (\Exception $e){
            throw new \Exception("Cron Job :".$e->getMessage());
        }
    }
}
