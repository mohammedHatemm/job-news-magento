<?php
namespace Magento\TwoFactorAuth\Model\AdminAccessTokenService;

/**
 * Interceptor class for @see \Magento\TwoFactorAuth\Model\AdminAccessTokenService
 */
class Interceptor extends \Magento\TwoFactorAuth\Model\AdminAccessTokenService implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\TwoFactorAuth\Api\TfaInterface $tfa, \Magento\TwoFactorAuth\Api\UserConfigRequestManagerInterface $configRequestManager, \Magento\User\Model\UserFactory $userFactory, \Magento\Integration\Api\AdminTokenServiceInterface $adminTokenService)
    {
        $this->___init();
        parent::__construct($tfa, $configRequestManager, $userFactory, $adminTokenService);
    }

    /**
     * {@inheritdoc}
     */
    public function createAdminAccessToken($username, $password) : string
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'createAdminAccessToken');
        return $pluginInfo ? $this->___callPlugins('createAdminAccessToken', func_get_args(), $pluginInfo) : parent::createAdminAccessToken($username, $password);
    }
}
