<?php

class AsApplication extends AsResource
{
    protected $client = null;
    
    function __construct(AsClient $client, array $data)
    {
        parent::__construct($data);
        $this->client = $client;
    }
    
    public function getId()
    {
        return $this->data['id'];
    }
    
    public function getSecret()
    {
        return $this->data['secret'];
    }
    
    public function getStreamById($stream_id)
    {
        $streams = $this->client->get($this->getLink('streams'), array('stream_id' => $stream_id));
        
        if (count($streams) == 0)
        {
            throw new Exception('Cannot find stream with id ' . $stream_id);
        }
        
        return new AsStream($this, $streams[0]);
    }

    public function createStream($id, array $values = array())
    {
        $values['id'] = $id;
        $stream = $this->client->post($this->getLink('streams'), $values);
        return new AsStream($this, $stream);
    }

    public function deleteStream(AsStream $stream)
    {
        $this->client->delete($stream->getLink('delete'));
    }

    public function getObjectById($object_id)
    {
        $objects = $this->client->get($this->getLink('objects'), array('object_id' => $object_id));
        
        if (count($objects) == 0)
        {
            throw new Exception('Cannot find object with id ' . $object_id);
        }
        
        return new AsObject($this, $objects[0]);
    }

    public function createObject($id, array $values = array())
    {
        $values['id'] = $id;
        $object = $this->client->post($this->getLink('objects'), $values);
        return new AsObject($this, $object);
    }
    
    public function deleteObject(AsObject $object)
    {
        $this->client->delete($object->getLink('delete'));
    }
    
    public function createActivityInStream(AsStream $stream, array $values, AsObject $actor = null, AsObject $object = null, AsObject $target = null)
    {
        $values['stream_id'] = $stream->getId();
        $this->client->post($stream->getLink('activities'), $values);
    }
    
    public function getFeedForObject(AsObject $object, $offset = 0, $limit = 20)
    {
        $values = array(
            'offset' => $offset,
            'limit' => $limit
        );
        
        $raw_feed = $this->client->get($object->getLink('feed'), $values);
        
        $activities = array();
        
        foreach ($raw_feed['items'] as $raw_activity)
        {
            $activities[] = new AsActivity($this, $raw_activity);
        }
        
        return $activities;
    }
    
    public function subscribeObjectToStream(AsObject $object, AsStream $stream)
    {
        $this->client->post($stream->getLink('subscribers'), array('object_id' => $object->getId()));
    }

    public function unsubscribeObjectFromStream(AsObject $object, $stream)
    {
        $subscriptions = $this->client->get($object->getLink('subscriptions'));

        foreach ($subscriptions as $subscription_data)
        {
            $subscription = new AsSubscription($this, $subscription_data);
            if ($subscription->getStreamId() == $stream->getId() && $subscription->getObjectId() == $object->getId())
            {
                $this->client->delete($subscription->getLink('unsubscribe'));
            }
        }
    }


}
