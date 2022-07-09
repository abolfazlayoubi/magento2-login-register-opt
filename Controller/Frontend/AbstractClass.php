<?php


namespace Magesoft\Otp\Controller\Frontend;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;
use Magesoft\Otp\Model\OtpManager;
use Magento\Customer\Model\Visitor;

abstract class AbstractClass
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManagerInterface;

    /**
     * @var OtpManager
     */
    protected $otpManager;

    /**
     * @var Visitor
     */
    protected $visitor;

    /**
     * @param Context $context
     * @param Session $session
     * @param SessionManagerInterface $sessionManagerInterface
     * @param OtpManager $otpManager
     * @param Visitor $visitor
     */
    public function __construct(
        Context $context,
        Session $session,
        SessionManagerInterface $sessionManagerInterface,
        OtpManager $otpManager,
        Visitor $visitor
    )
    {
        $this->context=$context;
        $this->session=$session;
        $this->otpManager=$otpManager;
        $this->sessionManagerInterface=$sessionManagerInterface;
        $this->visitor=$visitor;
    }
}
