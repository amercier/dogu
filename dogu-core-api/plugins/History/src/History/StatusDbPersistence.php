<?php

namespace History;

use PhlyRestfully\Exception\CreationException;
use PhlyRestfully\Exception\UpdateException;
use PhlyRestfully\Exception\PatchException;
use Zend\Db\Exception\ExceptionInterface as DbException;
use Zend\Db\TableGateway\TableGatewayInterface as TableGateway;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Stdlib\Hydrator\ClassMethods as ClassMethodsHydrator;

class StatusDbPersistence implements
    ListenerAggregateInterface,
    StatusPersistenceInterface
{
    /**
     * @var ClassMethodsHydrator
     */
    protected $hydrator;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var TableGateway
     */
    protected $table;

    /**
     * @var StatusValidator
     */
    protected $validator;

    public function __construct(TableGateway $table)
    {
        $this->table = $table;
        $this->validator = new StatusValidator();
        $this->hydrator  = new ClassMethodsHydrator();
    }

    public function attach(EventManagerInterface $events)
    {
        $events->attach('create', array($this, 'onCreate'));
        $events->attach('update', array($this, 'onUpdate'));
        $events->attach('patch', array($this, 'onPatch'));
        $events->attach('delete', array($this, 'onDelete'));
        $events->attach('fetch', array($this, 'onFetch'));
        $events->attach('fetchAll', array($this, 'onFetchAll'));
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function onCreate($e)
    {
        $user = $e->getRouteParam('user', false);
        if (!$user) {
            throw new CreationException('User must be specified in order to create a status');
        }
        if (false === $data = $e->getParam('data', false)) {
            throw new CreationException('Missing data');
        }

        $data = (array) $data;

        // Inject user into data
        $data['user'] = $user;

        $status = new Status();
        $status = $this->hydrator->hydrate($data, $status);
        if (!$this->validator->isValid($status)) {
            throw new CreationException('Status failed validation');
        }
        $data = $this->hydrator->extract($status);
        try {
            $this->table->insert($data);
        } catch (DbException $exception) {
            throw new CreationException('DB exception when creating status', null, $exception);
        }

        return $data;
    }

    public function onUpdate($e)
    {
        $user = $e->getRouteParam('user', false);
        if (!$user) {
            throw new UpdateException('User must be specified in order to update a status');
        }
        if (false === $id = $e->getParam('id', false)) {
            throw new UpdateException('Missing id');
        }

        if (false === $data = $e->getParam('data', false)) {
            throw new UpdateException('Missing data');
        }

        $data     = (array) $data;
        $rowset   = $this->table->select(array('id' => $id));
        $original = $rowset->current();
        if (!$original) {
            throw new UpdateException('Cannot update; status not found', 404);
        }

        $updated = $this->hydrator->hydrate($data, new Status());
        $updated->setId($original->getId());
        $updated->setTimestamp($original->getTimestamp());

        if (!$this->validator->isValid($updated)) {
            throw new UpdateException('Updated status failed validation');
        }

        $data = $this->hydrator->extract($updated);
        try {
            $this->table->update($data, array('id' => $id));
        } catch (DbException $exception) {
            throw new UpdateException('DB exception when updating status', null, $exception);
        }

        return $updated;
    }

    public function onPatch($e)
    {
        $user = $e->getRouteParam('user', false);
        if (!$user) {
            throw new PatchException('User must be specified in order to patch a status');
        }

        if (false === $id = $e->getParam('id', false)) {
            throw new PatchException('Missing id');
        }

        if (false === $data = $e->getParam('data', false)) {
            throw new PatchException('Missing data');
        }

        $data     = (array) $data;
        $rowset   = $this->table->select(array('id' => $id));
        $original = $rowset->current();
        if (!$original) {
            throw new PatchException('Cannot patch; status not found', 404);
        }

        $allowedUpdates = array(
            'type'       => true,
            'text'       => true,
            'image_url'  => true,
            'link_url'   => true,
            'link_title' => true,
        );
        $updates = array_intersect_key($data, $allowedUpdates);

        $status = $this->hydrator->hydrate($updates, $original);
        if (!$this->validator->isValid($status)) {
            throw new PatchException('Patched status failed validation');
        }

        try {
            $this->table->update($updates, array('id' => $id));
        } catch (DbException $exception) {
            throw new PatchException('DB exception when updating status', null, $exception);
        }

        return $status;
    }

    public function onDelete($e)
    {
        $user = $e->getRouteParam('user', false);
        if (!$user) {
            return false;
        }
        if (false === $id = $e->getParam('id', false)) {
            return false;
        }

        if (!$this->table->delete(array('id' => $id))) {
            return false;
        }

        return true;
    }

    public function onFetch($e)
    {
        if (false === $id = $e->getParam('id', false)) {
            return false;
        }

        $criteria = array('id' => $id);
        $user     = $e->getRouteParam('user', false);
        if ($user) {
            $criteria['user'] = $user;
        }

        $rowset = $this->table->select($criteria);
        $item   = $rowset->current();
        if (!$item) {
            return false;
        }
        return $item;
    }

    public function onFetchAll($e)
    {
        $user = $e->getRouteParam('user', false);
        return $this->table->fetchAll($user);
    }
}
