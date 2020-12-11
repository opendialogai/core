<?php

use OpenDialogAi\Core\Graph\DGraph\DGraphClient;

return [
    'schema' => "
        <attributes>: string .
        <causes_action>: [uid] .
        <simulates_intent>: [uid] .
        <context>: string .
        <conversation_status>: string @index(exact) .
        <conversation_version>: int .
        <core.attribute.completes>: default .
        <core.attribute.order>: default .
        <core.attribute.repeating>: default .
        <ei_type>: string @index(exact) .
        <followed_by>: uid @reverse .
        <has_bot_participant>: [uid] @reverse .
        <has_condition>: [uid] .
        <has_interpreter>: [uid] .
        <has_opening_scene>: [uid] @reverse .
        <has_scene>: [uid] .
        <has_user_participant>: [uid] @reverse .
        <has_attribute>: [uid] .
        <id>: string @index(exact) .
        <instance_of>: uid @reverse .
        <update_of>: uid @reverse .
        <listens_for>: [uid] @reverse .
        <name>: default .
        <operation>: string .
        <parameters>: string .     
        <says>: [uid] @reverse .
        <having_conversation>: [uid] @reverse .
        <says_across_scenes>: [uid] @reverse .
        <listens_for_across_scenes>: [uid] @reverse .
        <user_attribute_type>: string .
        <user_attribute_value>: string .
                    
        type " . DGraphClient::CONDITION . " {
            attributes: string
            context: string
            ei_type: string
            id: string
            operation: string
            parameters: string
        }
        type " . DGraphClient::VIRTUAL_INTENT . " {
            id: string
            ei_type: string
        }
        type " . DGraphClient::INTENT . " {
            causes_action: [uid]
            simulates_intent: [uid]
            core.attribute.completes: default
            core.attribute.order: default
            core.attribute.repeating: default
            ei_type: string
            followed_by: uid
            has_condition: [uid]
            has_interpreter: [uid]
            id: string
        }
        type " . DGraphClient::PARTICIPANT . " {
            ei_type: string
            id: string
            listens_for: [uid]
            says: [uid]
            says_across_scenes: [uid]
            listens_for_across_scenes: [uid]
        }
        type " . DGraphClient::SCENE . " {
            ei_type: string
            id: string
            has_bot_participant: [uid]
            has_condition: [uid]
            has_user_participant: [uid]
        }
        type " . DGraphClient::CONVERSATION . " {
            conversation_status: string
            conversation_version: int
            ei_type: string
            has_condition: [uid]
            has_opening_scene: [uid]
            has_scene: [uid]
            id: string
            instance_of: uid
            update_of: uid
        }
        type " . DGraphClient::USER_ATTRIBUTE . " {
            id: string
            ei_type: string
            user_attribute_type: string
            user_attribute_value: string
        }
        type " . DGraphClient::USER . " {
            id: string
            ei_type: string
            has_attribute: [uid]
        }
    "
];
