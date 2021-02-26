<?php


return [
    "schema" => <<<'GQL'
type Scenario  {
    id: ID!
    od_id: String! @search(by:[hash])
    name: String! @search(by:[hash])
    description: String
    conversations: [Conversation] @hasInverse(field:scenario)
    active: Boolean!
    status: EditStatus!
    conditions: [Condition]
    behaviors: [String]
    default_interpreter: String
}

type Conversation  {
    id: ID!
    od_id: String! @search(by:[hash])
    name: String! @search(by:[hash])
    description: String
    active: Boolean!
    status: EditStatus!
    conditions: [Condition]
    behaviors: [String]
    scenario: Scenario
    scenes: [Scene] @hasInverse(field:conversation)
    default_interpreter: String
}

type Scene  {
    id: ID!
    od_id: String! @search(by:[hash])
    name: String! @search(by:[hash])
    description: String
    active: Boolean!
    status: EditStatus!
    conditions: [Condition]
    behaviors: [String]
    default_interpreter: String
    turns: [Turn] @hasInverse(field:scene)
    conversation: Conversation
}

type Turn  {
    id: ID!
    od_id: String! @search(by:[hash])
    name: String! @search(by:[hash])
    description: String
    active: Boolean
    status: EditStatus!
    conditions: [Condition]
    behaviors: [String]
    default_interpreter: String
    intents: [Intent] @hasInverse(field:turn)
    scene: Scene
    valid_origins: [Turn]
}

type Intent  {
    id: ID!
    od_id: String! @search(by:[hash])
    name: String @search(by:[hash])
    intepreter: String
    confidence: Float
    expected_attributes: [Attribute]
    conditions: [Condition]
    actions: [Action]
    turn: Turn
    speaker: ParticipantType!
    listener: ParticipantType!
    scene_transition: String
    completes: Boolean
    u_virtual: String
}

type Attribute  {
    id: ID!
    name: String
    type: String
    value: String
}

type CompositeAttribute  {
    id: ID!
    name: String
    type: String
    value: [Attribute]
}

type Condition  {
    id: ID!
    operation: String
    attributes: [Attribute]
}

type Action  {
    id: ID!
    name: String
    input_attributes: [Attribute]
    output_attributes: [Attribute]
}

type User  {
    id: ID!
    user_id: String! @id
    attributes: [Attribute]
    records: [UserHistoryRecord] @hasInverse(field:user)
}

type UserHistoryRecord  {
    id: ID!
    user: User
    timestamp: DateTime
    platform: String
    workspace: String
    utterance: String
    speaker: ParticipantType
    intent_id: String
    scenario_id: String
    conversation_id: String
    turn_id: String
    completed: Boolean
    actions_performed: [String]
    conditions_evaluated: [String]
    u_virtual: String
}

enum ParticipantType {
    EI_USER
    EI_APP
    EI_HUMAN_AGENT
}

enum SceneType {
    EI_OPEN
}

enum EditStatus {
    DRAFT
    PREVIEW
    LIVE
}
GQL

];
