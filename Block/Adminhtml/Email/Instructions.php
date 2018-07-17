<?php
/**
 * Created by PhpStorm.
 * User: hannupolonen
 * Date: 10/07/18
 * Time: 09:24
 */

namespace Nosto\Tagging\Block\Adminhtml\Email;

use Magento\Backend\Block\Template as BlockTemplate;
use Nosto\Tagging\Helper\Account as NostoHelperAccount;
use Nosto\Tagging\Helper\Scope as NostoHelperScope;
use Magento\Backend\Block\Template\Context as BlockContext;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\NotFoundException;
use Nosto\Helper\IframeHelper;
use Nosto\Nosto;
use Nosto\Tagging\Model\Meta\Account\Iframe\Builder as NostoIframeMetaBuilder;
use Nosto\Tagging\Model\User\Builder as NostoCurrentUserBuilder;


class Instructions extends BlockTemplate
{

    private $nostoHelperAccount;
    private $backendAuthSession;
    private $nostoIframeMetaBuilder;
    private $nostoCurrentUserBuilder;
    private $nostoHelperScope;

    /**
     * Constructor.
     *
     * @param BlockContext $context the context.
     * @param NostoHelperAccount $nostoHelperAccount the account helper.
     * @param Session $backendAuthSession
     * @param NostoIframeMetaBuilder $iframeMetaBuilder
     * @param NostoCurrentUserBuilder $nostoCurrentUserBuilder
     * @param NostoHelperScope $nostoHelperScope
     * @param array $data
     */
    public function __construct(
        BlockContext $context,
        NostoHelperAccount $nostoHelperAccount,
        Session $backendAuthSession,
        NostoIframeMetaBuilder $iframeMetaBuilder,
        NostoCurrentUserBuilder $nostoCurrentUserBuilder,
        NostoHelperScope $nostoHelperScope,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->nostoHelperAccount = $nostoHelperAccount;
        $this->backendAuthSession = $backendAuthSession;
        $this->nostoIframeMetaBuilder = $iframeMetaBuilder;
        $this->nostoCurrentUserBuilder = $nostoCurrentUserBuilder;
        $this->nostoHelperScope = $nostoHelperScope;
    }

    /**
     * Returns Nosto accounts and corresponding links
     *
     */
    public function getAccountsAndLinks()
    {
        $store = $this->nostoHelperScope->getSelectedStore($this->getRequest());
        $account = $this->nostoHelperAccount->findAccount($store);

        return $account;
    }
}
