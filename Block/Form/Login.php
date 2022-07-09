<?php

namespace Magesoft\Otp\Block\Form;
use Magento\Customer\Block\Form\Login as MainLoginClass;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
class Login extends MainLoginClass
{
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param Url $customerUrl
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Url $customerUrl,
        array $data = [])
    {
        parent::__construct($context, $customerSession, $customerUrl, $data);
    }

    /**
     * @return string
     */
    public function getSendCodeUrl(): string
    {
        return $this->getUrl('magesoftotp/frontend/sendcode');

    }

    /**
     * @return string
     */
    public function getCheckCodeUrl(): string
    {
        return $this->getUrl('magesoftotp/frontend/CheckCode');

    }

}
