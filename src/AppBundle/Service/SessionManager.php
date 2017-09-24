<?php

namespace AppBundle\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionManager
{
    private const IS_ASCENDING_KEY = 'isAscending';
    private const IS_ORDER_BY_DATE_KEY = 'isOrderByDate';

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        if (!($this->session->isStarted())) {
            $this->session->start();
        }
    }

    public function getIsAscending(): bool
    {
        return $this->getValue(self::IS_ASCENDING_KEY, false);
    }

    public function getIsOrderByDate(): bool
    {
        return $this->getValue(self::IS_ORDER_BY_DATE_KEY, true);
    }

    public function setIsAscending(bool $value)
    {
        $this->setValue(self::IS_ASCENDING_KEY, $value);
    }

    public function setIsOrderByDate(bool $value)
    {
        $this->setValue(self::IS_ORDER_BY_DATE_KEY, $value);
    }

    private function setValue(string $keyName, bool $value)
    {
        $this->session->set($keyName, $value);
    }

    private function getValue(string $keyName, bool $defaultValue): bool
    {
        if ($this->session->has($keyName)) {
            return $this->session->get($keyName);
        } else {
            $this->session->set($keyName, $defaultValue);
            return $defaultValue;
        }
    }
}