<?php
namespace Riftfox\Wechat\CheckSession;

use Riftfox\Wechat\Application\ApplicationInterface;
use Riftfox\Wechat\Session\SessionInterface;
use Riftfox\Wechat\SessionSignature\SignatureFactoryInterface;

interface CheckSessionProviderInterface
{
    const string CHECK_SESSION_URL = "https://api.weixin.qq.com/wxa/checksession";
    
    public function checkSession(ApplicationInterface $application,SessionInterface $session, string $sigMethod = SignatureFactoryInterface::METHOD_HMAC_SHA256): bool;
}