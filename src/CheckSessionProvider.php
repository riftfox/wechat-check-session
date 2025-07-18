<?php
namespace Riftfox\Wechat\CheckSession;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Riftfox\Wechat\Application\ApplicationInterface;
use Riftfox\Wechat\Exception\ExceptionFactoryInterface;
use Riftfox\Wechat\Session\SessionInterface;
use Riftfox\Wechat\SessionSignature\SignatureFactoryInterface;

class CheckSessionProvider implements CheckSessionProviderInterface
{
    private ClientInterface $httpClient;
    private RequestFactoryInterface $requestFactory;
    private UriFactoryInterface $uriFactory;
    private ExceptionFactoryInterface $exceptionFactory;
    private SignatureFactoryInterface $signatureFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        SignatureFactoryInterface $signatureFactory,
        ExceptionFactoryInterface $exceptionFactory
    ) {
        $this->httpClient = $client;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->exceptionFactory = $exceptionFactory;
        $this->signatureFactory = $signatureFactory;
    }

    public function checkSession(ApplicationInterface $application,SessionInterface $session,string $sigMethod = SignatureFactoryInterface::METHOD_HMAC_SHA256):bool
    {
        $request = $this->getRequest($application, $session, $sigMethod);
        $response = $this->httpClient->sendRequest($request);
        $data = json_decode($response->getBody()->getContents(), true);
        
        // 如果是业务逻辑错误（如 session_key 无效），返回结果
        // 如果是其他错误（如网络错误），抛出异常
        if (isset($data['errcode']) && $data['errcode'] !== 0 ) {
            throw $this->exceptionFactory->createException($data['errmsg'], $data['errcode']);
        }
        return true;
    }

    private function getRequest(ApplicationInterface $application, SessionInterface $session, string $sigMethod = SignatureFactoryInterface::METHOD_HMAC_SHA256): RequestInterface
    {
        $uri = $this->uriFactory->createUri(self::CHECK_SESSION_URL);
        $uri = $uri->withQuery(http_build_query([
            'access_token' => $application->getToken(),
            'openid' => $session->getOpenId(),
            'sig_method' => $sigMethod,
            'signature' => $this->signatureFactory->createSignature($sigMethod)->sign($session)
        ]));
        
        return $this->requestFactory->createRequest('GET', $uri);
    }
}