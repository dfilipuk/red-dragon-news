<?php

namespace AppBundle\Service;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionManager
{
    private const IS_ASCENDING_KEY = 'isAscending';
    private const IS_ORDER_BY_DATE_KEY = 'isOrderByDate';

    private $session;

    /**
     * SessionManager constructor.
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
        if (!($this->session->isStarted())) {
            $this->session->start();
        }
    }

    /**
     * @return bool
     */
    public function getIsAscending(): bool
    {
        return $this->getValue(self::IS_ASCENDING_KEY, false);
    }

    /**
     * @return bool
     */
    public function getIsOrderByDate(): bool
    {
        return $this->getValue(self::IS_ORDER_BY_DATE_KEY, true);
    }

    /**
     * @param bool $value
     */
    public function setIsAscending(bool $value): void
    {
        $this->setValue(self::IS_ASCENDING_KEY, $value);
    }

    /**
     * @param bool $value
     */
    public function setIsOrderByDate(bool $value): void
    {
        $this->setValue(self::IS_ORDER_BY_DATE_KEY, $value);
    }

    /**
     * @param string $keyName
     * @param bool $value
     */
    private function setValue(string $keyName, bool $value): void
    {
        $this->session->set($keyName, $value);
    }

    /**
     * @param string $keyName
     * @param bool $defaultValue
     * @return bool
     */
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