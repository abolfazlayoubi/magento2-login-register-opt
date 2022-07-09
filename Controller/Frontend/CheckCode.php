<?php


namespace Magesoft\Otp\Controller\Frontend;
use Magento\Framework\App\ActionInterface;

class CheckCode extends AbstractClass implements ActionInterface
{

    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        try {
            $params=$this->context->getRequest()
                ->getParams();
            if ($this->otpManager->checkRequestBefore($this->visitor->getId(),$params['mobile'])){

                $id=$this->otpManager->getCustomerIdByItemId($params['code'],$this->visitor->getId());
                if ($id){
                    $this->session->loginById($id);
                }
                return $this->context->getResultFactory()->create(
                    $this->context->getResultFactory()::TYPE_JSON)
                    ->setHttpResponseCode(200)
                    ->setData([
                        'message'=>__('login ok'),
                    ])
                    ;
            }else{
                return $this->context->getResultFactory()->create(
                    $this->context->getResultFactory()::TYPE_JSON)
                    ->setHttpResponseCode(400)
                    ->setData([
                        'message'=>__('Code doest not fount try again'),
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
