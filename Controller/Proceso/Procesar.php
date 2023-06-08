<?php
namespace DavidDelgado\GoogleCoordinates\Controller\Proceso;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use DavidDelgado\GoogleCoordinates\Model\Carrier\MiEnvio;
use Magento\Framework\Session\SessionManagerInterface;

class Procesar extends Action
{
    protected $resultJsonFactory;
    protected $MiEnvio;
    protected $session;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        MiEnvio $MiEnvio,
        SessionManagerInterface $session
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->MiEnvio = $MiEnvio;
        $this->session = $session;
    }

    public function execute()
    {
        $userData = $this->getRequest()->getPostValue('userData');
        // Realizar el procesamiento necesario con los datos
        $this->session->setUserData($userData);
        // Enviar una respuesta de vuelta a JavaScript si es necesario
        $response = ['success' => true, 'message' => $userData];
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
