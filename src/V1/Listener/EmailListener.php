<?php
namespace Strapieno\UserRecoverPassword\Api\V1\Listener;

use MailMan\Message;
use Matryoshka\Model\ModelEvent;
use Matryoshka\Model\Object\ActiveRecord\ActiveRecordInterface;
use Strapieno\User\Model\Entity\UserInterface;
use Strapieno\Utils\Model\Entity\IdentityExistAwareInterface;
use Zend\EventManager\Event;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\ViewModel;
use ZF\Hal\Entity;

/**
 * Class EmailListener
 */
class EmailListener implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    use ListenerAggregateTrait;
    use ServiceLocatorAwareTrait;

    /**
     * @var string
     */
    protected $subject;

    /**
     * @var string
     */
    protected $template;

    /**
     *  {@inheritdoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(ModelEvent::EVENT_SAVE_POST, [$this, 'sendValidationMail']);
    }

    /**
     * @param Event $e
     */
    public function sendValidationMail(Event $e)
    {
        $hal = $e->getParam('entity');
        if ($hal instanceof Entity
            && ($user = $hal->entity)
            && $user instanceof IdentityExistAwareInterface
            && $user instanceof ActiveRecordInterface
            && $user instanceof UserInterface)
        {
            $message = $this->getMessage($user);
            $this->getServiceLocator()->get('MailMan\Service\MailInterface')->send($message);
        }
    }

    /**
     * @param IdentityExistAwareInterface $user
     * @return ViewModel
     */
    protected function getTemplateMail(IdentityExistAwareInterface $user)
    {
        $template = new ViewModel();
        $template->setVariable('user', $user);
        $template->setTemplate($this->getTemplate());

        return $template;
    }

    /**
     * @param UserInterface $user
     * @return Message
     */
    protected function getMessage(UserInterface $user)
    {
        $viewRenderer = $this->getServiceLocator()->get('ViewRenderer');

        $message = new Message();
        $message->setSubject($this->getSubject());
        $message->setTo($user->getEmail());
        $message->addHtmlPart($viewRenderer->render($this->getTemplateMail($user)));

        return $message;
    }

    public function getServiceLocator()
    {
        if ($this->serviceLocator instanceof AbstractPluginManager) {
            return $this->serviceLocator->getServiceLocator();
        }
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }
}