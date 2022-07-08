<?php


namespace Magesoft\Otp\Model\Queue;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\CallbackInvoker;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\QueueInterface;
use Magesoft\Otp\Model\Method\Logger;
use Magesoft\Otp\Model\OtpManager;
class SmsConsumer implements ConsumerInterface
{

    /**
     * @var \Magento\Framework\MessageQueue\CallbackInvoker
     */
    private $invoker;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\MessageQueue\ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var \Magento\Framework\MessageQueue\MessageController
     */
    private $messageController;

    protected $json;

    protected $customerRepository;

    protected $kosarJsonStructure;
    protected $request;
    protected $installData;
    protected $orderRepository;
    protected $abstractConsumer;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OtpManager
     */
    protected $otpManager;

    /**
     * Map constructor.
     * @param CallbackInvoker $invoker
     * @param ResourceConnection $resource
     * @param MessageController $messageController
     * @param ConsumerConfigurationInterface $configuration
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param Logger $logger
     * @param OtpManager $otpManager
     */
    public function __construct(
        CallbackInvoker $invoker,
        ResourceConnection $resource,
        MessageController $messageController,
        ConsumerConfigurationInterface $configuration,
        \Magento\Framework\Serialize\Serializer\Json $json,
        Logger $logger,
        OtpManager $otpManager
    ) {
        $this->invoker = $invoker;
        $this->resource = $resource;
        $this->messageController = $messageController;
        $this->configuration = $configuration;
        $this->json=$json;
        $this->logger=$logger;
        $this->otpManager=$otpManager;
    }


    /**
     * Connects to a queue, consumes a message on the queue, and invoke a method to process the message contents.
     *
     * @param int|null $maxNumberOfMessages if not specified - process all queued incoming messages and terminate,
     *      otherwise terminate execution after processing the specified number of messages
     * @return void
     * @since 102.0.5
     */
    /**
     * {@inheritdoc}
     */
    public function process($maxNumberOfMessages = null)
    {
        $queue = $this->configuration->getQueue();
        if (!isset($maxNumberOfMessages)) {
            $queue->subscribe($this->getTransactionCallback($queue));
        } else {
            $this->invoker->invoke($queue, $maxNumberOfMessages, $this->getTransactionCallback($queue));
        }
    }

    /**
     * Get transaction callback. This handles the case of async.
     *
     * @param QueueInterface $queue
     * @return \Closure
     */
    private function getTransactionCallback(QueueInterface $queue)
    {
        return function (EnvelopeInterface $message) use ($queue) {

            /** @var LockInterface $lock */
            $lock = null;
            try {
                $lock = $this->messageController->lock($message, $this->configuration->getConsumerName());
                /**
                 * $this->processQueueMsg->process() use for process message which you publish in queue
                 */

                $response=$this->mapMessage($message->getBody());
                if ($response){
                    $queue->acknowledge($message); // send acknowledge to queue
                }
            } catch (MessageLockException $exception) {
            } catch (ConnectionLostException $e) {
                if ($lock) {
                    $this->resource->getConnection()
                        ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
                }
            } catch (NotFoundException $e) {
                $this->logger->error($e->getMessage());
            } catch (\Exception $e) {
                $queue->reject($message, false, $e->getMessage());
                if ($lock) {
                    $this->resource->getConnection()
                        ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
                }
            }
        };
    }

    /**
     * @param string $data
     * @return bool
     */
    public function mapMessage(string $data):bool
    {
        try{
            $data=json_decode($this->json->unserialize($data));
            $this->otpManager->sendSms($data['id'],$data['mobile'],$data['auth_code']);
            return true;
        }catch (\Exception $e){
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}
