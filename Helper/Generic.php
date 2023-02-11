<?php
namespace Kgkrunch\StripeIntegration\Helper;

class Generic extends \StripeIntegration\Payments\Helper\Generic
{
	public function holdOrder(&$order)
    {
        $order->setHoldBeforeState($order->getState());
        $order->setHoldBeforeStatus($order->getStatus());
        $order->setState(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW)
            ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW));
        $comment = __("Order placed under manual review by Stripe Radar.");
        $order->addStatusToHistory(false, $comment, false);

        $pi = $this->cleanToken($order->getPayment()->getLastTransId());
        if (!empty($pi))
        {
            // @todo: Why are we doing this inside holdOrder() ?
            $paymentIntent = $this->paymentIntentFactory->create();
            $paymentIntent->load($pi, 'pi_id'); // Finds or creates the row
            $paymentIntent->setPiId($pi);
            $paymentIntent->setOrderIncrementId($order->getIncrementId());
            $paymentIntent->setQuoteId($order->getQuoteId());
            $paymentIntent->save();
        }

        return $order;
    }

}