<?php

namespace Magesoft\Otp\Controller\Frontend;

use Magento\Framework\App\ActionInterface;

class SendCode extends AbstractClass implements ActionInterface
{


    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $params=$this->context->getRequest()
                ->getParams();
            if (!$this->otpManager->checkRequestBefore($this->visitor->getId(),$params['mobile'])){
                $idRow=$this->otpManager->generateCodeAndSend(
                    intval($this->visitor->getId()),
                    $params['mobile']);
                return $this->context->getResultFactory()->create(
                    $this->context->getResultFactory()::TYPE_JSON)
                    ->setHttpResponseCode(201)
                    ->setData([
                        'id'=>$idRow
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
