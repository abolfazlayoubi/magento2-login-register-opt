<?php

namespace Magesoft\Otp\Controller\Frontend;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ActionInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Session\SessionManagerInterface;
use Magesoft\Otp\Model\OtpManager;
use Magento\Customer\Model\Visitor;
class SendCode implements ActionInterface
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

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $params=$this->context->getRequest()
                ->getParams();
            if ($this->otpManager->checkRequestBefore($this->visitor->getId(),$params['mobile'])){
                $this->otpManager->generateCodeAndSend(
                    intval($this->visitor->getId()),
                    $params['mobile']);
                return $this->context->getResultFactory()->create(
                    $this->context->getResultFactory()::TYPE_JSON)
                    ->setHttpResponseCode(201)
                    ->setData([
                        'mobile'=>1,
                        'visitor_id'=>$this->sessionManagerInterface
                                ->getVisitorData()['visitor_id']??0
                    ])
                    ;
            }else{
                return $this->context->getResultFactory()->create(
                    $this->context->getResultFactory()::TYPE_JSON)
                    ->setHttpResponseCode(400)
                    ->setData([
                        'message'=>__('send request before'),
                    ])
                    ;
            }

        }catch (\Exception $e){
            return $this->context->getResultFactory()->create(
                $this->context->getResultFactory()::TYPE_JSON)
                ->setData([
                    'message'=>$e->getMessage()
                ])
                ->setHttpResponseCode(400);
        }
    }
}
