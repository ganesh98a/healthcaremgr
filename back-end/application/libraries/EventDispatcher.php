<?php

class EventDispatcher {

    public function __construct() {
        $this->CI=& get_instance();
        $this->events = [
            'onAfterApplicationCreated' => [
                'Application' => [ //object
                    'record_created',
                    'record_created_or_updated'
                ]
            ],
            'onAfterApplicantCreated' => [
                'Applicant' => [ //object
                    'record_created',
                    'record_created_or_updated'
                ]
            ],
            'onAfterApplicantUpdated' => [
                                            'Applicant' => [ //object
                                                'record_created_or_updated'
                                            ]
                                        ],
            'onAfterShiftCreated' => [
                                        'Shift' => [ //object
                                            'record_created',
                                            'record_created_or_updated'
                                        ]
                                    ],
            'onAfterShiftUpdated' => [
                                        'Shift' => [ //object
                                            'record_created_or_updated'
                                        ]
                                    ],
            'onAfterGroupbookingUpdated' => [
                                            'GroupBooking' => [ //object
                                                'record_created_or_updated'
                                            ]
                                        ]
                                                
            
        ];
    }

    public function getEventMap($event) {
        return $this->events[$event];
    }

    public function dispatch($event, $id, $data, $previousValues = null) {
        $event_map = $this->getEventMap($event);
        $this->CI->load->model('admin/Process_management_model');
        $event_object = array_keys($event_map)[0];
        $triggers = $event_map[$event_object];
        $this->CI->Process_management_model->executeEvent($event_object, $triggers, $id, $data, $previousValues);
        return false;
    }
}