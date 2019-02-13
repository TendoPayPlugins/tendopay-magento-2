<?php
/**
 * TendoPay
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customize this module for your needs.
 *
 * @category   TendoPay
 * @package    TendoPay_TendopayPayment
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace TendoPay\TendopayPayment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class UpgradeSchema
 * @package TendoPay\TendopayPayment\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            $setup->startSetup();

            if ($setup->getConnection()->tableColumnExists('quote_payment', 'tendopay_token') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('quote_payment'),
                    'tendopay_token',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Order Token'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('quote_payment', 'tendopay_order_id') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('quote_payment'),
                    'tendopay_order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Order ID'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order_payment', 'tendopay_token') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order_payment'),
                    'tendopay_token',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Hash'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order_payment', 'tendopay_order_id') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order_payment'),
                    'tendopay_order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Order ID'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order_payment', 'tendopay_disposition') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order_payment'),
                    'tendopay_disposition',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Status'
                    ]
                );
            }

            if ($setup->getConnection()
                    ->tableColumnExists('sales_order_payment', 'tendopay_verification_token') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order_payment'),
                    'tendopay_verification_token',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Verification Token'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order_payment', 'tendopay_fetched_at') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order_payment'),
                    'tendopay_fetched_at',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'length' => null,
                        'nullable' => false,
                        'comment' => 'TendoPay Fetch Time'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order', 'tendopay_token') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order'),
                    'tendopay_token',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Hash'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order', 'tendopay_order_id') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order'),
                    'tendopay_order_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Order ID'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order', 'tendopay_disposition') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order'),
                    'tendopay_disposition',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Status'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order', 'tendopay_verification_token') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order'),
                    'tendopay_verification_token',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'nullable' => true,
                        'comment' => 'TendoPay Verification Token'
                    ]
                );
            }

            if ($setup->getConnection()->tableColumnExists('sales_order', 'tendopay_fetched_at') === false) {
                $setup->getConnection()->addColumn(
                    $setup->getTable('sales_order'),
                    'tendopay_fetched_at',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                        'length' => null,
                        'nullable' => false,
                        'comment' => 'TendoPay Fetch Time'
                    ]
                );
            }
            /**
             * Insert new Status for order
             */
            $data = [];
            $statuses = [
                'tendopay_payment_review' => 'TendoPay Processing'
            ];
            foreach ($statuses as $code => $info) {
                $data[] = ['status' => $code, 'label' => $info];
            }

            $setup->getConnection()->delete($setup->getTable('sales_order_status'), "status = '" . $code . "'");
            $setup->getConnection()
                ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

            /**
             * Insert new State for order
             */
            $stateData = [];
            $states = [
                'tendopay_payment_review' => 'payment_review'
            ];
            foreach ($states as $code => $info) {
                $stateData[] = ['status' => $code, 'state' => $info, 'is_default' => 0];
            }
            $setup->getConnection()->delete($setup->getTable('sales_order_status_state'), "status = '" . $code . "'");
            $setup->getConnection()
                ->insertArray(
                    $setup->getTable('sales_order_status_state'),
                    ['status', 'state', 'is_default'],
                    $stateData
                );

            $setup->endSetup();
        }
    }
}
