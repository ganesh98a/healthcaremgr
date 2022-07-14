<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ClassParticipantHelping;

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Description of ParticipantHelping
 *
 * @author Corner stone solutions
 */
class ParticipantHelping {

    function make_place_data($participantPlace, $placeLabel) {
        $x = $y = array();
        foreach ($participantPlace as $val) {
            if ($val->type == '1') {
                $x[] = $placeLabel[$val->placeId];
            } elseif ($val->type == '2') {
                $y[] = $placeLabel[$val->placeId];
            }
        }


        $place['fav'] = (!empty($x)) ? implode(', ', $x) : '';
        $place['least_fav'] = (!empty($y)) ? implode(', ', $y) : '';

        return $place;
    }

    function make_activiy_data($participantPlace, $placeLabel) {
        $x = $y = array();

        foreach ($participantPlace as $val) {
            if ($val->type == '1') {
                $x[] = $placeLabel[$val->activityId];
            } elseif ($val->type == '2') {
                $y[] = $placeLabel[$val->activityId];
            }
        }

        $place['fav'] = (!empty($x)) ? implode(', ', $x) : '';
        $place['least_fav'] = (!empty($y)) ? implode(', ', $y) : '';

        return $place;
    }

    function make_asistance_data($participantAsistance, $asistanceLabel) {
        $temp = array();

        foreach ($participantAsistance as $val) {
            $temp[] = $asistanceLabel[$val->assistanceId];
        }

        $asistance = implode(', ', $temp);

        return $asistance;
    }

    function make_support_data($data, $label) {
        $temp = array();
        foreach ($data as $val) {
            $temp[] = $label[$val->support_required];
        }
        return $return = implode(', ', $temp);
    }

    function make_oc_service_data($data, $label) {
        $temp = array();
        foreach ($data as $val) {
            $temp[] = $label[$val->oc_service];
        }
        return $return = implode(', ', $temp);
    }

    function make_mobility_data($data, $label) {
        $temp = array();
        foreach ($data as $val) {
            $temp[] = $label[$val->assistanceId];
        }
        return $return = implode(', ', $temp);
    }

    function make_email_data($data) {
        $temp = array();
        foreach ($data as $val) {
            if ($val->primary_email == 1) {
                $temp[] = $val->email . ' (Primary)';
            } else {
                $temp[] = $val->email;
            }
        }
        return $return = implode(', ', $temp);
    }

    function make_phone_data($data) {
        $temp = array();
        foreach ($data as $val) {
            if ($val->primary_phone == 1) {
                $temp[] = $val->phone . ' (Primary)';
            } else {
                $temp[] = $val->phone;
            }
        }
        return $return = implode(', ', $temp);
    }

    function make_address_data($data, $label) {
        $temp = array();
        foreach ($data as $val) {
            if ($val->primary_phone == 1) {
                $temp[] = $val->phone . ' (Primary)';
            } else {
                $temp[] = $val->phone;
            }
        }
        return $return = implode(', ', $temp);
    }

    function asistance($oldData, $newData, $label) {
        $element_data = array();

        $asistanceOld = $this->make_asistance_data($oldData, $label);
        $asistanceNew = $this->make_asistance_data($newData, $label);

        if ($asistanceOld == $asistanceNew) {
            return false;
        }

        $i = 0;
        $element_data['description'] = "Profile Update";
        $element_data['area'] = "Care Requirements";
        $element_data['field'] = 'Assistance Required';
        $element_data['previous'] = $asistanceOld;
        $element_data['new'] = $asistanceNew;
        $element_data['approve'] = false;
        $element_data['subcomponet'] = 'CommonDisplay';
        $element_data['key'] = 'asistance';

        return $element_data;
    }

    function support_required($oldData, $newData, $label) {
        $element_data = array();

        $oldData = $this->make_support_data($oldData, $label);
        $newData = $this->make_support_data($newData, $label);

        if ($oldData == $newData) {
            return false;
        }

        $i = 0;
        $element_data['description'] = "Profile Update";
        $element_data['area'] = "Care Requirements";
        $element_data['field'] = 'Support Required';
        $element_data['previous'] = $oldData;
        $element_data['new'] = $newData;
        $element_data['approve'] = false;
        $element_data['subcomponet'] = 'CommonDisplay';
        $element_data['key'] = 'support_required';

        return $element_data;
    }

    function oc_service($oldData, $newData, $label) {
        $element_data = array();

        $oc_service_old = $this->make_oc_service_data($oldData, $label);
        $oc_service_new = $this->make_oc_service_data($newData, $label);

        if ($oc_service_old == $oc_service_new) {
            return false;
        }

        $i = 0;
        $element_data['description'] = "Profile Update";
        $element_data['area'] = "Care Requirements";
        $element_data['field'] = 'OC Service';
        $element_data['previous'] = $oc_service_old;
        $element_data['new'] = $oc_service_new;
        $element_data['approve'] = false;
        $element_data['subcomponet'] = 'CommonDisplay';
        $element_data['key'] = 'oc_service';

        return $element_data;
    }

    function mobility($oldData, $newData, $label) {
        $element_data = array();

        $mobility_old = $this->make_mobility_data($oldData, $label);
        $mobility_new = $this->make_mobility_data($newData, $label);

        if ($mobility_old == $mobility_new) {
            return false;
        }

        $i = 0;
        $element_data['description'] = "Profile Update";
        $element_data['area'] = "Care Requirements";
        $element_data['field'] = 'Mobility';
        $element_data['previous'] = $mobility_old;
        $element_data['new'] = $mobility_new;
        $element_data['approve'] = false;
        $element_data['subcomponet'] = 'CommonDisplay';
        $element_data['key'] = 'mobility';

        return $element_data;
    }

    function preferred_language($language_list, $old, $new) {
        if ($old == $new) {
            return false;
        }

        $element_data = array();
        $element_data['description'] = "Profile Update";
        $element_data['area'] = "Care Requirements";
        $element_data['field'] = 'Preferred Language';
        $element_data['previous'] = $old;
        $element_data['new'] = $new;
        $element_data['previous_value'] = $language_list[$old];
        $element_data['new_value'] = $language_list[$new];
        $element_data['approve'] = false;
        $element_data['subcomponet'] = 'ParticipantPreferredLanguage';
        $element_data['key'] = 'preferred_language';

        return $element_data;
    }

    function linguistic_interpreter($old, $new) {
        if ($old == $new) {
            return false;
        }

        $element_data = array();

        $element_data['description'] = "Profile Update";
        $element_data['area'] = "Care Requirements";
        $element_data['field'] = 'Linguistic Interpreter';
        $element_data['previous'] = $old;
        $element_data['new'] = $new;
        $element_data['approve'] = false;
        $element_data['subcomponet'] = 'ParticipantLinguisticInterpreter';
        $element_data['key'] = 'linguistic_interpreter';

        return $element_data;
    }

    function hearing_interpreter($old, $new) {
        if ($old == $new) {
            return false;
        }

        $element_data = array();

        $element_data['description'] = "Profile Update";
        $element_data['area'] = "Care Requirements";
        $element_data['field'] = 'Hearing Interpreter';
        $element_data['previous'] = $old;
        $element_data['new'] = $new;
        $element_data['approve'] = false;
        $element_data['subcomponet'] = 'ParticipantHearingInterpreter';
        $element_data['key'] = 'hearing_interpreter';

        return $element_data;
    }

}
