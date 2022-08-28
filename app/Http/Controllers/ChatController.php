<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Aws\LexRuntimeService\LexRuntimeServiceClient;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private $bot_config;
    private $sender_bot_name;
    private $sender_bot_id;

    public function __construct()
    {
        $this->bot_config = [
            'region'   => env('AWS_DEFAULT_REGION'),
            'version'  => env('LEX_BOT_VERSION'),
            'botName'  => env('LEX_BOT_NAME'),
            'botAlias' => env('LEX_BOT_ALIAS')
        ];

        $this->sender_bot_name = env('LEX_BOT_NAME');
        $this->sender_bot_id = env('LEX_BOT_ALIAS');
    }

    public function init(Request $request)
    {
        $lexRuntimeServiceClient = new LexRuntimeServiceClient($this->bot_config);

        $userId = Auth::id();
        $this->bot_config['userId'] = 'client-' . $userId;
        $newSessionData = $lexRuntimeServiceClient->putSession($this->bot_config);

        $messages = Chat::create([
            "session_id"  => $newSessionData["sessionId"],
            'message'     => "Initializing a new message.",
            'sender_name' => $this->sender_bot_name,
            'sender_id'   => $this->sender_bot_id,
            'receiver_id' => Auth::id(),
            'user_type' => 'bot'
        ]);

        return response()->json([
                "dialogState" => $newSessionData["dialogState"],
                "messages"    => [$messages],
                "slots"       => $newSessionData["slots"],
        ], Response::HTTP_OK);
    }

    public function send(Request $request)
    {
        $lexRuntimeServiceClient = new LexRuntimeServiceClient($this->bot_config);

        $newMessage = $this->bot_config;
        $newMessage['inputText'] = $request->message;
        $newMessage['userId'] = 'client-' . Auth::id();

        $returnMessage = $lexRuntimeServiceClient->postText($newMessage);

        $newMessage = [
            [
                'message'     => $request->message,
                'sender_id'   => Auth::id(),
                'sender_name' => Auth::user()->name,
                'session_id'  => $returnMessage['sessionId'],
                'receiver_id' => $this->sender_bot_id,
                'user_type'   => 'human'
            ],
        ];

        if ($returnMessage["dialogState"] != "ReadyForFulfillment") {
            $newMessage[] =
                [
                    'message'     => $returnMessage["message"],
                    'sender_name' => $this->sender_bot_name,
                    'sender_id'   => $this->sender_bot_id,
                    'session_id'  => $returnMessage['sessionId'],
                    'receiver_id' => Auth::id(),
                    'user_type'   => 'bot'
                ];
        }

        $messsages = [];
        foreach ($newMessage as $key => $value) {
            $messsages[] = Chat::create([
                'session_id'  => $returnMessage["sessionId"],
                'message'     => $value["message"],
                'sender_id'   => $value["sender_id"],
                'sender_name' => $value["sender_name"],
                'receiver_id' => $value["receiver_id"],
                'user_type'   => $value["user_type"]
            ]);
        }

        return response()->json([
            "dialogState" => $returnMessage["dialogState"],
            "slots"       => $returnMessage["slots"],
            "messages"    => $messsages
        ], Response::HTTP_OK);
    }
}