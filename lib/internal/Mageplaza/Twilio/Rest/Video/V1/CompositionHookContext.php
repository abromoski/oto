<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Video\V1;

use Twilio\Exceptions\TwilioException;
use Twilio\InstanceContext;
use Twilio\Options;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

/**
 * PLEASE NOTE that this class contains preview products that are subject to change. Use them with caution. If you currently do not have developer preview access, please contact help@twilio.com.
 */
class CompositionHookContext extends InstanceContext {
    /**
     * Initialize the CompositionHookContext
     *
     * @param \Twilio\Version $version Version that contains the resource
     * @param string $sid The SID that identifies the resource to fetch
     * @return \Twilio\Rest\Video\V1\CompositionHookContext
     */
    public function __construct(Version $version, $sid) {
        parent::__construct($version);

        // Path Solution
        $this->solution = array('sid' => $sid, );

        $this->uri = '/CompositionHooks/' . \rawurlencode($sid) . '';
    }

    /**
     * Fetch a CompositionHookInstance
     *
     * @return CompositionHookInstance Fetched CompositionHookInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function fetch() {
        $params = Values::of(array());

        $payload = $this->version->fetch(
            'GET',
            $this->uri,
            $params
        );

        return new CompositionHookInstance($this->version, $payload, $this->solution['sid']);
    }

    /**
     * Deletes the CompositionHookInstance
     *
     * @return boolean True if delete succeeds, false otherwise
     * @throws TwilioException When an HTTP error occurs.
     */
    public function delete() {
        return $this->version->delete('delete', $this->uri);
    }

    /**
     * Update the CompositionHookInstance
     *
     * @param string $friendlyName A unique string to describe the resource
     * @param array|Options $options Optional Arguments
     * @return CompositionHookInstance Updated CompositionHookInstance
     * @throws TwilioException When an HTTP error occurs.
     */
    public function update($friendlyName, $options = array()) {
        $options = new Values($options);

        $data = Values::of(array(
            'FriendlyName' => $friendlyName,
            'Enabled' => Serialize::booleanToString($options['enabled']),
            'VideoLayout' => Serialize::jsonObject($options['videoLayout']),
            'AudioSources' => Serialize::map($options['audioSources'], function($e) { return $e; }),
            'AudioSourcesExcluded' => Serialize::map($options['audioSourcesExcluded'], function($e) { return $e; }),
            'Trim' => Serialize::booleanToString($options['trim']),
            'Format' => $options['format'],
            'Resolution' => $options['resolution'],
            'StatusCallback' => $options['statusCallback'],
            'StatusCallbackMethod' => $options['statusCallbackMethod'],
        ));

        $payload = $this->version->update(
            'POST',
            $this->uri,
            array(),
            $data
        );

        return new CompositionHookInstance($this->version, $payload, $this->solution['sid']);
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString() {
        $context = array();
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Video.V1.CompositionHookContext ' . \implode(' ', $context) . ']';
    }
}