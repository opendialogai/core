<?php

namespace OpenDialogAi\ConversationEngine\tests;

use OpenDialogAi\ConversationEngine\ConversationStore\EIModelConversationConverter;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModelCreator;
use OpenDialogAi\ConversationEngine\ConversationStore\EIModels\EIModelConversation;
use OpenDialogAi\Core\Tests\TestCase;

class ConversationBuildingTest extends TestCase
{
    public function testBuildComplicatedConversation()
    {
        $conversation = $this->getComplicatedConversation();

        $modelCreator = app()->make(EIModelCreator::class);

        /* @var EIModelConversation $conversationModel */
        $conversationModel = $modelCreator->createEIModel(EIModelConversation::class, $conversation);

        $conversationConverter = app()->make(EIModelConversationConverter::class);
        $conversationConverter::buildConversationFromEIModel($conversationModel, false);

        $this->assertTrue(true);
    }

    public function testNothing()
    {
        $this->assertTrue(true);
    }

    private function getComplicatedConversation(): array
    {
        return json_decode(<<< EOT
{
  "uid": "0x5275",
  "id": "welcome_conversation",
  "ei_type": "conversation_user",
  "has_opening_scene": [
    {
      "uid": "0x51ff",
      "id": "opening_scene",
      "has_user_participant": [
        {
          "uid": "0x5205",
          "id": "user_participant_in_opening_scene",
          "says": [
            {
              "uid": "0x524e",
              "id": "intent.core.welcome",
              "core.attribute.order": "1",
              "core.attribute.completes": ""
            }
          ],
          "says_across_scenes": [
            {
              "uid": "0x5214",
              "id": "intent.datingBot.continue",
              "core.attribute.order": "4",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5263",
                  "id": "bot_participant_in_name_scene",
                  "~has_bot_participant": [
                    {
                      "id": "name_scene"
                    }
                  ]
                }
              ]
            },
            {
              "uid": "0x525a",
              "id": "intent.datingBot.stop",
              "core.attribute.order": "5",
              "core.attribute.completes": "",
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51fc",
                  "id": "bot_participant_in_close_scene",
                  "~has_bot_participant": [
                    {
                      "id": "close_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5221",
              "id": "intent.datingBot.NewUserWelcome",
              "core.attribute.order": "2",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5251",
          "id": "bot_participant_in_opening_scene",
          "says": [
            {
              "uid": "0x5221",
              "id": "intent.datingBot.NewUserWelcome",
              "core.attribute.order": "2",
              "core.attribute.completes": ""
            }
          ],
          "listens_for": [
            {
              "uid": "0x524e",
              "id": "intent.core.welcome",
              "core.attribute.order": "1",
              "core.attribute.completes": ""
            }
          ]
        }
      ]
    }
  ],
  "has_scene": [
    {
      "uid": "0x51f4",
      "id": "close_scene",
      "has_user_participant": [
        {
          "uid": "0x5211",
          "id": "user_participant_in_close_scene",
          "listens_for": [
            {
              "uid": "0x5256",
              "id": "intent.datingBot.endConversation",
              "core.attribute.order": "60",
              "core.attribute.completes": "1"
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x51fc",
          "id": "bot_participant_in_close_scene",
          "says": [
            {
              "uid": "0x5256",
              "id": "intent.datingBot.endConversation",
              "core.attribute.order": "60",
              "core.attribute.completes": "1"
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x520d",
              "id": "intent.datingBot.FreeConsultationAnswer",
              "core.attribute.order": "59",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5217",
                  "id": "user.free_consultation"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51fc",
                  "id": "bot_participant_in_close_scene",
                  "~has_bot_participant": [
                    {
                      "id": "close_scene"
                    }
                  ]
                }
              ]
            },
            {
              "uid": "0x525a",
              "id": "intent.datingBot.stop",
              "core.attribute.order": "5",
              "core.attribute.completes": "",
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51fc",
                  "id": "bot_participant_in_close_scene",
                  "~has_bot_participant": [
                    {
                      "id": "close_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x51f5",
      "id": "sex_important_scene",
      "has_user_participant": [
        {
          "uid": "0x5218",
          "id": "user_participant_in_sex_important_scene",
          "says_across_scenes": [
            {
              "uid": "0x5225",
              "id": "intent.datingBot.SexImportantAnswer",
              "core.attribute.order": "49",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5235",
                  "id": "user.sex_important"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5253",
                  "id": "bot_participant_in_sex_time_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sex_time_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5226",
              "id": "intent.datingBot.SexImportantQuestion",
              "core.attribute.order": "48",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x521d",
          "id": "bot_participant_in_sex_important_scene",
          "says": [
            {
              "uid": "0x5226",
              "id": "intent.datingBot.SexImportantQuestion",
              "core.attribute.order": "48",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5294",
              "id": "intent.datingBot.SexPartnersAnswer",
              "core.attribute.order": "47",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5215",
                  "id": "user.sex_partners"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x521d",
                  "id": "bot_participant_in_sex_important_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sex_important_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x520a",
      "id": "name_scene",
      "has_user_participant": [
        {
          "uid": "0x525b",
          "id": "user_participant_in_name_scene",
          "says_across_scenes": [
            {
              "uid": "0x51fb",
              "id": "intent.datingBot.NameAnswer",
              "core.attribute.order": "7",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x526b",
                  "id": "user.name"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x525e",
                  "id": "bot_participant_in_job_scene",
                  "~has_bot_participant": [
                    {
                      "id": "job_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5278",
              "id": "intent.datingBot.NameQuestion",
              "core.attribute.order": "6",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5263",
          "id": "bot_participant_in_name_scene",
          "says": [
            {
              "uid": "0x5278",
              "id": "intent.datingBot.NameQuestion",
              "core.attribute.order": "6",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5214",
              "id": "intent.datingBot.continue",
              "core.attribute.order": "4",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5263",
                  "id": "bot_participant_in_name_scene",
                  "~has_bot_participant": [
                    {
                      "id": "name_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x520b",
      "id": "sex_time_scene",
      "has_user_participant": [
        {
          "uid": "0x5210",
          "id": "user_participant_in_sex_time_scene",
          "says_across_scenes": [
            {
              "uid": "0x520f",
              "id": "intent.datingBot.SexTimeAnswer",
              "core.attribute.order": "51",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5255",
                  "id": "user.sex_time"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5237",
                  "id": "bot_participant_in_addiction_scene",
                  "~has_bot_participant": [
                    {
                      "id": "addiction_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x522e",
              "id": "intent.datingBot.SexTimeQuestion",
              "core.attribute.order": "50",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5253",
          "id": "bot_participant_in_sex_time_scene",
          "says": [
            {
              "uid": "0x522e",
              "id": "intent.datingBot.SexTimeQuestion",
              "core.attribute.order": "50",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5225",
              "id": "intent.datingBot.SexImportantAnswer",
              "core.attribute.order": "49",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5235",
                  "id": "user.sex_important"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5253",
                  "id": "bot_participant_in_sex_time_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sex_time_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x520e",
      "id": "free_consultation_scene",
      "has_user_participant": [
        {
          "uid": "0x51f6",
          "id": "user_participant_in_free_consultation_scene",
          "says_across_scenes": [
            {
              "uid": "0x520d",
              "id": "intent.datingBot.FreeConsultationAnswer",
              "core.attribute.order": "59",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5217",
                  "id": "user.free_consultation"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51fc",
                  "id": "bot_participant_in_close_scene",
                  "~has_bot_participant": [
                    {
                      "id": "close_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x523e",
              "id": "intent.datingBot.FreeConsultationQuestion",
              "core.attribute.order": "58",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5234",
          "id": "bot_participant_in_free_consultation_scene",
          "says": [
            {
              "uid": "0x523e",
              "id": "intent.datingBot.FreeConsultationQuestion",
              "core.attribute.order": "58",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5243",
              "id": "intent.datingBot.TalkWithExpertAnswer",
              "core.attribute.order": "57",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5272",
                  "id": "user.talk_with_expert"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5234",
                  "id": "bot_participant_in_free_consultation_scene",
                  "~has_bot_participant": [
                    {
                      "id": "free_consultation_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5220",
      "id": "family_important_scene",
      "has_user_participant": [
        {
          "uid": "0x5242",
          "id": "user_participant_in_family_important_scene",
          "says_across_scenes": [
            {
              "uid": "0x524f",
              "id": "intent.datingBot.FamilyImportantAnswer",
              "core.attribute.order": "37",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5250",
                  "id": "user.family_important"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f3",
                  "id": "bot_participant_in_want_children_scene",
                  "~has_bot_participant": [
                    {
                      "id": "want_children_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x523d",
              "id": "intent.datingBot.FamilyImportantQuestion",
              "core.attribute.order": "36",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x523c",
          "id": "bot_participant_in_family_important_scene",
          "says": [
            {
              "uid": "0x523d",
              "id": "intent.datingBot.FamilyImportantQuestion",
              "core.attribute.order": "36",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x520c",
              "id": "intent.datingBot.FuturePlansAnswer",
              "core.attribute.order": "35",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5252",
                  "id": "user.future_plans"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x523c",
                  "id": "bot_participant_in_family_important_scene",
                  "~has_bot_participant": [
                    {
                      "id": "family_important_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5228",
      "id": "age_scene",
      "has_user_participant": [
        {
          "uid": "0x5254",
          "id": "user_participant_in_age_scene",
          "says_across_scenes": [
            {
              "uid": "0x5284",
              "id": "intent.datingBot.AgeAnswer",
              "core.attribute.order": "11",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5266",
                  "id": "user.age"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x522c",
                  "id": "bot_participant_in_color_eyes_scene",
                  "~has_bot_participant": [
                    {
                      "id": "color_eyes_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5295",
              "id": "intent.datingBot.AgeQuestion",
              "core.attribute.order": "10",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5257",
          "id": "bot_participant_in_age_scene",
          "says": [
            {
              "uid": "0x5295",
              "id": "intent.datingBot.AgeQuestion",
              "core.attribute.order": "10",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5238",
              "id": "intent.datingBot.JobAnswer",
              "core.attribute.order": "9",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5239",
                  "id": "user.job"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5257",
                  "id": "bot_participant_in_age_scene",
                  "~has_bot_participant": [
                    {
                      "id": "age_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x522f",
      "id": "job_scene",
      "has_user_participant": [
        {
          "uid": "0x5280",
          "id": "user_participant_in_job_scene",
          "says_across_scenes": [
            {
              "uid": "0x5238",
              "id": "intent.datingBot.JobAnswer",
              "core.attribute.order": "9",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5239",
                  "id": "user.job"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5257",
                  "id": "bot_participant_in_age_scene",
                  "~has_bot_participant": [
                    {
                      "id": "age_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5232",
              "id": "intent.datingBot.JobQuestion",
              "core.attribute.order": "8",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x525e",
          "id": "bot_participant_in_job_scene",
          "says": [
            {
              "uid": "0x5232",
              "id": "intent.datingBot.JobQuestion",
              "core.attribute.order": "8",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x51fb",
              "id": "intent.datingBot.NameAnswer",
              "core.attribute.order": "7",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x526b",
                  "id": "user.name"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x525e",
                  "id": "bot_participant_in_job_scene",
                  "~has_bot_participant": [
                    {
                      "id": "job_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x523b",
      "id": "future_plans_scene",
      "has_user_participant": [
        {
          "uid": "0x5212",
          "id": "user_participant_in_future_plans_scene",
          "says_across_scenes": [
            {
              "uid": "0x520c",
              "id": "intent.datingBot.FuturePlansAnswer",
              "core.attribute.order": "35",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5252",
                  "id": "user.future_plans"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x523c",
                  "id": "bot_participant_in_family_important_scene",
                  "~has_bot_participant": [
                    {
                      "id": "family_important_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5270",
              "id": "intent.datingBot.FuturePlansQuestion",
              "core.attribute.order": "34",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5207",
          "id": "bot_participant_in_future_plans_scene",
          "says": [
            {
              "uid": "0x5270",
              "id": "intent.datingBot.FuturePlansQuestion",
              "core.attribute.order": "34",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5224",
              "id": "intent.datingBot.SportsAnswer",
              "core.attribute.order": "33",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5291",
                  "id": "user.sports"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5207",
                  "id": "bot_participant_in_future_plans_scene",
                  "~has_bot_participant": [
                    {
                      "id": "future_plans_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5245",
      "id": "addiction_scene",
      "has_user_participant": [
        {
          "uid": "0x525c",
          "id": "user_participant_in_addiction_scene",
          "says_across_scenes": [
            {
              "uid": "0x529d",
              "id": "intent.datingBot.AddictionAnswer",
              "core.attribute.order": "53",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x521b",
                  "id": "user.addiction"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f2",
                  "id": "bot_participant_in_email_scene",
                  "~has_bot_participant": [
                    {
                      "id": "email_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x527d",
              "id": "intent.datingBot.AddictionQuestion",
              "core.attribute.order": "52",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5237",
          "id": "bot_participant_in_addiction_scene",
          "says": [
            {
              "uid": "0x527d",
              "id": "intent.datingBot.AddictionQuestion",
              "core.attribute.order": "52",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x520f",
              "id": "intent.datingBot.SexTimeAnswer",
              "core.attribute.order": "51",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5255",
                  "id": "user.sex_time"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5237",
                  "id": "bot_participant_in_addiction_scene",
                  "~has_bot_participant": [
                    {
                      "id": "addiction_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5246",
      "id": "sports_scene",
      "has_user_participant": [
        {
          "uid": "0x5265",
          "id": "user_participant_in_sports_scene",
          "says_across_scenes": [
            {
              "uid": "0x5224",
              "id": "intent.datingBot.SportsAnswer",
              "core.attribute.order": "33",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5291",
                  "id": "user.sports"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5207",
                  "id": "bot_participant_in_future_plans_scene",
                  "~has_bot_participant": [
                    {
                      "id": "future_plans_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5274",
              "id": "intent.datingBot.SportsQuestion",
              "core.attribute.order": "32",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x526f",
          "id": "bot_participant_in_sports_scene",
          "says": [
            {
              "uid": "0x5274",
              "id": "intent.datingBot.SportsQuestion",
              "core.attribute.order": "32",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5290",
              "id": "intent.datingBot.DoSportsAnswer",
              "core.attribute.order": "31",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x527c",
                  "id": "user.do_sports"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x526f",
                  "id": "bot_participant_in_sports_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sports_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5247",
      "id": "talk_with_expert_scene",
      "has_user_participant": [
        {
          "uid": "0x525f",
          "id": "user_participant_in_talk_with_expert_scene",
          "says_across_scenes": [
            {
              "uid": "0x5243",
              "id": "intent.datingBot.TalkWithExpertAnswer",
              "core.attribute.order": "57",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5272",
                  "id": "user.talk_with_expert"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5234",
                  "id": "bot_participant_in_free_consultation_scene",
                  "~has_bot_participant": [
                    {
                      "id": "free_consultation_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x523f",
              "id": "intent.datingBot.TalkWithExpertQuestion",
              "core.attribute.order": "56",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5258",
          "id": "bot_participant_in_talk_with_expert_scene",
          "says": [
            {
              "uid": "0x523f",
              "id": "intent.datingBot.TalkWithExpertQuestion",
              "core.attribute.order": "56",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5285",
              "id": "intent.datingBot.EmailAnswer",
              "core.attribute.order": "55",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x521c",
                  "id": "user.email"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5258",
                  "id": "bot_participant_in_talk_with_expert_scene",
                  "~has_bot_participant": [
                    {
                      "id": "talk_with_expert_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5248",
      "id": "color_hair_scene",
      "has_user_participant": [
        {
          "uid": "0x5240",
          "id": "user_participant_in_color_hair_scene",
          "says_across_scenes": [
            {
              "uid": "0x529c",
              "id": "intent.datingBot.ColorHairAnswer",
              "core.attribute.order": "15",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5277",
                  "id": "user.color_hair"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x529b",
                  "id": "bot_participant_in_height_scene",
                  "~has_bot_participant": [
                    {
                      "id": "height_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x528d",
              "id": "intent.datingBot.ColorHairQuestion",
              "core.attribute.order": "14",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x51f1",
          "id": "bot_participant_in_color_hair_scene",
          "says": [
            {
              "uid": "0x528d",
              "id": "intent.datingBot.ColorHairQuestion",
              "core.attribute.order": "14",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5213",
              "id": "intent.datingBot.ColorEyesAnswer",
              "core.attribute.order": "13",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5209",
                  "id": "user.color_eyes"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f1",
                  "id": "bot_participant_in_color_hair_scene",
                  "~has_bot_participant": [
                    {
                      "id": "color_hair_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5249",
      "id": "sex_partners_scene",
      "has_user_participant": [
        {
          "uid": "0x5200",
          "id": "user_participant_in_sex_partners_scene",
          "says_across_scenes": [
            {
              "uid": "0x5294",
              "id": "intent.datingBot.SexPartnersAnswer",
              "core.attribute.order": "47",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5215",
                  "id": "user.sex_partners"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x521d",
                  "id": "bot_participant_in_sex_important_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sex_important_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5288",
              "id": "intent.datingBot.SexPartnersQuestion",
              "core.attribute.order": "46",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x51f8",
          "id": "bot_participant_in_sex_partners_scene",
          "says": [
            {
              "uid": "0x5288",
              "id": "intent.datingBot.SexPartnersQuestion",
              "core.attribute.order": "46",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5222",
              "id": "intent.datingBot.SexualOrientationAnswer",
              "core.attribute.order": "45",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x522a",
                  "id": "user.sexual_orientation"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f8",
                  "id": "bot_participant_in_sex_partners_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sex_partners_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x524a",
      "id": "weight_scene",
      "has_user_participant": [
        {
          "uid": "0x51fe",
          "id": "user_participant_in_weight_scene",
          "says_across_scenes": [
            {
              "uid": "0x5241",
              "id": "intent.datingBot.WeightAnswer",
              "core.attribute.order": "19",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5201",
                  "id": "user.weight"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x525d",
                  "id": "bot_participant_in_hobbies_scene",
                  "~has_bot_participant": [
                    {
                      "id": "hobbies_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5204",
              "id": "intent.datingBot.WeightQuestion",
              "core.attribute.order": "18",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x528a",
          "id": "bot_participant_in_weight_scene",
          "says": [
            {
              "uid": "0x5204",
              "id": "intent.datingBot.WeightQuestion",
              "core.attribute.order": "18",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5279",
              "id": "intent.datingBot.HeightAnswer",
              "core.attribute.order": "17",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5262",
                  "id": "user.height"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x528a",
                  "id": "bot_participant_in_weight_scene",
                  "~has_bot_participant": [
                    {
                      "id": "weight_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x524d",
      "id": "time_single_scene",
      "has_user_participant": [
        {
          "uid": "0x5282",
          "id": "user_participant_in_time_single_scene",
          "says_across_scenes": [
            {
              "uid": "0x524c",
              "id": "intent.datingBot.TimeSingleAnswer",
              "core.attribute.order": "41",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x528b",
                  "id": "user.time_single"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x523a",
                  "id": "bot_participant_in_relationship_scene",
                  "~has_bot_participant": [
                    {
                      "id": "relationship_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x51f7",
              "id": "intent.datingBot.TimeSingleQuestion",
              "core.attribute.order": "40",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5298",
          "id": "bot_participant_in_time_single_scene",
          "says": [
            {
              "uid": "0x51f7",
              "id": "intent.datingBot.TimeSingleQuestion",
              "core.attribute.order": "40",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x527e",
              "id": "intent.datingBot.WantChildrenAnswer",
              "core.attribute.order": "39",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x51f0",
                  "id": "user.want_children"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5298",
                  "id": "bot_participant_in_time_single_scene",
                  "~has_bot_participant": [
                    {
                      "id": "time_single_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5259",
      "id": "residence_scene",
      "has_user_participant": [
        {
          "uid": "0x5271",
          "id": "user_participant_in_residence_scene",
          "says_across_scenes": [
            {
              "uid": "0x5233",
              "id": "intent.datingBot.ResidenceAnswer",
              "core.attribute.order": "23",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5296",
                  "id": "user.residence"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5297",
                  "id": "bot_participant_in_earning_scene",
                  "~has_bot_participant": [
                    {
                      "id": "earning_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5229",
              "id": "intent.datingBot.ResidenceQuestion",
              "core.attribute.order": "22",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x526d",
          "id": "bot_participant_in_residence_scene",
          "says": [
            {
              "uid": "0x5229",
              "id": "intent.datingBot.ResidenceQuestion",
              "core.attribute.order": "22",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x526e",
              "id": "intent.datingBot.HobbiesAnswer",
              "core.attribute.order": "21",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x524b",
                  "id": "user.hobbies"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x526d",
                  "id": "bot_participant_in_residence_scene",
                  "~has_bot_participant": [
                    {
                      "id": "residence_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5260",
      "id": "do_sports_scene",
      "has_user_participant": [
        {
          "uid": "0x5231",
          "id": "user_participant_in_do_sports_scene",
          "says_across_scenes": [
            {
              "uid": "0x5290",
              "id": "intent.datingBot.DoSportsAnswer",
              "core.attribute.order": "31",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x527c",
                  "id": "user.do_sports"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x526f",
                  "id": "bot_participant_in_sports_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sports_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5216",
              "id": "intent.datingBot.DoSportsQuestion",
              "core.attribute.order": "30",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x522b",
          "id": "bot_participant_in_do_sports_scene",
          "says": [
            {
              "uid": "0x5216",
              "id": "intent.datingBot.DoSportsQuestion",
              "core.attribute.order": "30",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x528f",
              "id": "intent.datingBot.EatingAnswer",
              "core.attribute.order": "29",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x526a",
                  "id": "user.eating"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x522b",
                  "id": "bot_participant_in_do_sports_scene",
                  "~has_bot_participant": [
                    {
                      "id": "do_sports_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5268",
      "id": "color_eyes_scene",
      "has_user_participant": [
        {
          "uid": "0x521f",
          "id": "user_participant_in_color_eyes_scene",
          "says_across_scenes": [
            {
              "uid": "0x5213",
              "id": "intent.datingBot.ColorEyesAnswer",
              "core.attribute.order": "13",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5209",
                  "id": "user.color_eyes"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f1",
                  "id": "bot_participant_in_color_hair_scene",
                  "~has_bot_participant": [
                    {
                      "id": "color_hair_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x529e",
              "id": "intent.datingBot.ColorEyesQuestion",
              "core.attribute.order": "12",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x522c",
          "id": "bot_participant_in_color_eyes_scene",
          "says": [
            {
              "uid": "0x529e",
              "id": "intent.datingBot.ColorEyesQuestion",
              "core.attribute.order": "12",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5284",
              "id": "intent.datingBot.AgeAnswer",
              "core.attribute.order": "11",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5266",
                  "id": "user.age"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x522c",
                  "id": "bot_participant_in_color_eyes_scene",
                  "~has_bot_participant": [
                    {
                      "id": "color_eyes_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5269",
      "id": "hobbies_scene",
      "has_user_participant": [
        {
          "uid": "0x5281",
          "id": "user_participant_in_hobbies_scene",
          "says_across_scenes": [
            {
              "uid": "0x526e",
              "id": "intent.datingBot.HobbiesAnswer",
              "core.attribute.order": "21",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x524b",
                  "id": "user.hobbies"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x526d",
                  "id": "bot_participant_in_residence_scene",
                  "~has_bot_participant": [
                    {
                      "id": "residence_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x522d",
              "id": "intent.datingBot.HobbiesQuestion",
              "core.attribute.order": "20",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x525d",
          "id": "bot_participant_in_hobbies_scene",
          "says": [
            {
              "uid": "0x522d",
              "id": "intent.datingBot.HobbiesQuestion",
              "core.attribute.order": "20",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5241",
              "id": "intent.datingBot.WeightAnswer",
              "core.attribute.order": "19",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5201",
                  "id": "user.weight"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x525d",
                  "id": "bot_participant_in_hobbies_scene",
                  "~has_bot_participant": [
                    {
                      "id": "hobbies_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5276",
      "id": "height_scene",
      "has_user_participant": [
        {
          "uid": "0x521a",
          "id": "user_participant_in_height_scene",
          "says_across_scenes": [
            {
              "uid": "0x5279",
              "id": "intent.datingBot.HeightAnswer",
              "core.attribute.order": "17",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5262",
                  "id": "user.height"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x528a",
                  "id": "bot_participant_in_weight_scene",
                  "~has_bot_participant": [
                    {
                      "id": "weight_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5230",
              "id": "intent.datingBot.HeightQuestion",
              "core.attribute.order": "16",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x529b",
          "id": "bot_participant_in_height_scene",
          "says": [
            {
              "uid": "0x5230",
              "id": "intent.datingBot.HeightQuestion",
              "core.attribute.order": "16",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x529c",
              "id": "intent.datingBot.ColorHairAnswer",
              "core.attribute.order": "15",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5277",
                  "id": "user.color_hair"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x529b",
                  "id": "bot_participant_in_height_scene",
                  "~has_bot_participant": [
                    {
                      "id": "height_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x527b",
      "id": "want_children_scene",
      "has_user_participant": [
        {
          "uid": "0x526c",
          "id": "user_participant_in_want_children_scene",
          "says_across_scenes": [
            {
              "uid": "0x527e",
              "id": "intent.datingBot.WantChildrenAnswer",
              "core.attribute.order": "39",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x51f0",
                  "id": "user.want_children"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5298",
                  "id": "bot_participant_in_time_single_scene",
                  "~has_bot_participant": [
                    {
                      "id": "time_single_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5273",
              "id": "intent.datingBot.WantChildrenQuestion",
              "core.attribute.order": "38",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x51f3",
          "id": "bot_participant_in_want_children_scene",
          "says": [
            {
              "uid": "0x5273",
              "id": "intent.datingBot.WantChildrenQuestion",
              "core.attribute.order": "38",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x524f",
              "id": "intent.datingBot.FamilyImportantAnswer",
              "core.attribute.order": "37",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5250",
                  "id": "user.family_important"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f3",
                  "id": "bot_participant_in_want_children_scene",
                  "~has_bot_participant": [
                    {
                      "id": "want_children_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x527f",
      "id": "earning_scene",
      "has_user_participant": [
        {
          "uid": "0x5206",
          "id": "user_participant_in_earning_scene",
          "says_across_scenes": [
            {
              "uid": "0x5223",
              "id": "intent.datingBot.EarningAnswer",
              "core.attribute.order": "25",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5289",
                  "id": "user.earning"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5219",
                  "id": "bot_participant_in_languages_scene",
                  "~has_bot_participant": [
                    {
                      "id": "languages_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5264",
              "id": "intent.datingBot.EarningQuestion",
              "core.attribute.order": "24",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5297",
          "id": "bot_participant_in_earning_scene",
          "says": [
            {
              "uid": "0x5264",
              "id": "intent.datingBot.EarningQuestion",
              "core.attribute.order": "24",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5233",
              "id": "intent.datingBot.ResidenceAnswer",
              "core.attribute.order": "23",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5296",
                  "id": "user.residence"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5297",
                  "id": "bot_participant_in_earning_scene",
                  "~has_bot_participant": [
                    {
                      "id": "earning_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5286",
      "id": "email_scene",
      "has_user_participant": [
        {
          "uid": "0x5208",
          "id": "user_participant_in_email_scene",
          "says_across_scenes": [
            {
              "uid": "0x5285",
              "id": "intent.datingBot.EmailAnswer",
              "core.attribute.order": "55",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x521c",
                  "id": "user.email"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5258",
                  "id": "bot_participant_in_talk_with_expert_scene",
                  "~has_bot_participant": [
                    {
                      "id": "talk_with_expert_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x51fd",
              "id": "intent.datingBot.EmailQuestion",
              "core.attribute.order": "54",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x51f2",
          "id": "bot_participant_in_email_scene",
          "says": [
            {
              "uid": "0x51fd",
              "id": "intent.datingBot.EmailQuestion",
              "core.attribute.order": "54",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x529d",
              "id": "intent.datingBot.AddictionAnswer",
              "core.attribute.order": "53",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x521b",
                  "id": "user.addiction"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f2",
                  "id": "bot_participant_in_email_scene",
                  "~has_bot_participant": [
                    {
                      "id": "email_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5287",
      "id": "eating_scene",
      "has_user_participant": [
        {
          "uid": "0x5244",
          "id": "user_participant_in_eating_scene",
          "says_across_scenes": [
            {
              "uid": "0x528f",
              "id": "intent.datingBot.EatingAnswer",
              "core.attribute.order": "29",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x526a",
                  "id": "user.eating"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x522b",
                  "id": "bot_participant_in_do_sports_scene",
                  "~has_bot_participant": [
                    {
                      "id": "do_sports_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5203",
              "id": "intent.datingBot.EatingQuestion",
              "core.attribute.order": "28",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x51f9",
          "id": "bot_participant_in_eating_scene",
          "says": [
            {
              "uid": "0x5203",
              "id": "intent.datingBot.EatingQuestion",
              "core.attribute.order": "28",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x51fa",
              "id": "intent.datingBot.LanguagesAnswer",
              "core.attribute.order": "27",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5267",
                  "id": "user.languages"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f9",
                  "id": "bot_participant_in_eating_scene",
                  "~has_bot_participant": [
                    {
                      "id": "eating_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x528e",
      "id": "relationship_scene",
      "has_user_participant": [
        {
          "uid": "0x5299",
          "id": "user_participant_in_relationship_scene",
          "says_across_scenes": [
            {
              "uid": "0x528c",
              "id": "intent.datingBot.RelationshipAnswer",
              "core.attribute.order": "43",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5283",
                  "id": "user.relationship"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5236",
                  "id": "bot_participant_in_sexual_orientation_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sexual_orientation_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x521e",
              "id": "intent.datingBot.RelationshipQuestion",
              "core.attribute.order": "42",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x523a",
          "id": "bot_participant_in_relationship_scene",
          "says": [
            {
              "uid": "0x521e",
              "id": "intent.datingBot.RelationshipQuestion",
              "core.attribute.order": "42",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x524c",
              "id": "intent.datingBot.TimeSingleAnswer",
              "core.attribute.order": "41",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x528b",
                  "id": "user.time_single"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x523a",
                  "id": "bot_participant_in_relationship_scene",
                  "~has_bot_participant": [
                    {
                      "id": "relationship_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5292",
      "id": "languages_scene",
      "has_user_participant": [
        {
          "uid": "0x527a",
          "id": "user_participant_in_languages_scene",
          "says_across_scenes": [
            {
              "uid": "0x51fa",
              "id": "intent.datingBot.LanguagesAnswer",
              "core.attribute.order": "27",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5267",
                  "id": "user.languages"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f9",
                  "id": "bot_participant_in_eating_scene",
                  "~has_bot_participant": [
                    {
                      "id": "eating_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5227",
              "id": "intent.datingBot.LanguagesQuestion",
              "core.attribute.order": "26",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5219",
          "id": "bot_participant_in_languages_scene",
          "says": [
            {
              "uid": "0x5227",
              "id": "intent.datingBot.LanguagesQuestion",
              "core.attribute.order": "26",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x5223",
              "id": "intent.datingBot.EarningAnswer",
              "core.attribute.order": "25",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5289",
                  "id": "user.earning"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5219",
                  "id": "bot_participant_in_languages_scene",
                  "~has_bot_participant": [
                    {
                      "id": "languages_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    },
    {
      "uid": "0x5293",
      "id": "sexual_orientation_scene",
      "has_user_participant": [
        {
          "uid": "0x529a",
          "id": "user_participant_in_sexual_orientation_scene",
          "says_across_scenes": [
            {
              "uid": "0x5222",
              "id": "intent.datingBot.SexualOrientationAnswer",
              "core.attribute.order": "45",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x522a",
                  "id": "user.sexual_orientation"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x51f8",
                  "id": "bot_participant_in_sex_partners_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sex_partners_scene"
                    }
                  ]
                }
              ]
            }
          ],
          "listens_for": [
            {
              "uid": "0x5202",
              "id": "intent.datingBot.SexualOrientationQuestion",
              "core.attribute.order": "44",
              "core.attribute.completes": ""
            }
          ]
        }
      ],
      "has_bot_participant": [
        {
          "uid": "0x5236",
          "id": "bot_participant_in_sexual_orientation_scene",
          "says": [
            {
              "uid": "0x5202",
              "id": "intent.datingBot.SexualOrientationQuestion",
              "core.attribute.order": "44",
              "core.attribute.completes": ""
            }
          ],
          "listens_for_across_scenes": [
            {
              "uid": "0x528c",
              "id": "intent.datingBot.RelationshipAnswer",
              "core.attribute.order": "43",
              "core.attribute.completes": "",
              "has_interpreter": [
                {
                  "uid": "0x5261",
                  "id": "interpreter.datingBot.AnswerInterpreter"
                }
              ],
              "has_expected_attribute": [
                {
                  "uid": "0x5283",
                  "id": "user.relationship"
                }
              ],
              "~listens_for_across_scenes": [
                {
                  "uid": "0x5236",
                  "id": "bot_participant_in_sexual_orientation_scene",
                  "~has_bot_participant": [
                    {
                      "id": "sexual_orientation_scene"
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]
    }
  ]
}
EOT
            , true);
    }
}
