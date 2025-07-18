# wechat-check-session

微信小程序校验登录态 SDK，用于校验 session_key 是否有效。

## 功能特性

- 封装微信小程序 auth.checkSession 接口
- 支持 PSR-7/PSR-17/PSR-18 标准
- 提供统一的校验结果数据结构
- 支持 HMAC-SHA256 签名方法

## 安装

```bash
composer require riftfox/wechat-check-session
```

## 使用方法

```php
use Riftfox\Wechat\CheckSession\CheckSessionProvider;
use Riftfox\Wechat\CheckSession\Session;
use Riftfox\Wechat\Application\Application;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;

// 初始化依赖
$client = new Client();
$requestFactory = new Psr17Factory();
$uriFactory = new Psr17Factory();
$resultFactory = new ResultFactory();
$exceptionFactory = new ExceptionFactory();

// 创建 provider 实例
$provider = new CheckSessionProvider(
    $client,
    $requestFactory,
    $uriFactory,
    $resultFactory,
    $exceptionFactory
);

// 创建应用实例
$application = new Application('appid', 'secret', ApplicationInterface::TYPE_MINIAPP);

// 创建会话信息
$session = new Session();
$session->setSessionKey('your-session-key');

try {
    // 校验 session_key
    $result = $provider->checkSession($application, $session,SignatureFactoryInterface::METHOD_HMAC_SHA256);

    // 处理结果
    if ($result->isValid()) {
        echo "session_key 有效";
    } else {
        echo "session_key 已过期";
    }
} catch (Exception $e) {
    echo "请求失败：" . $e->getMessage();
}
```

## 接口说明

### CheckSessionProviderInterface

```php
interface CheckSessionProviderInterface 
{
    // 接口 URL 常量
    const CHECK_SESSION_URL = "https://api.weixin.qq.com/wxa/checksession";
    
    /**
     * 校验登录态是否有效
     *
     * @param ApplicationInterface $application 应用实例
     * @param SessionInterface $session 会话信息
     * @return ResultInterface 校验结果
     * @throws Exception 请求失败时抛出异常
     */
    public function checkSession(ApplicationInterface $application, SessionInterface $session, string $sigMethod=SignatureFactoryInterface::METHOD_HMAC_SHA256): ResultInterface;
}
```

### SessionInterface

会话信息接口，包含 session_key 等会话相关信息。

### ResultInterface

校验结果接口，用于判断 session_key 是否有效。

## 相关文档

- [小程序登录 - 校验登录态](https://developers.weixin.qq.com/miniprogram/dev/OpenApiDoc/user-login/checkSessionKey.html)

## License

MIT