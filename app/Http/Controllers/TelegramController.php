<?php

namespace App\Http\Controllers;

use App\Helpers\Command;
use App\Helpers\Util;

class TelegramController extends Controller
{
    private function telegramWebhook()
    {
        $is_send = false;
        $updates = json_decode(file_get_contents('php://input'), true);
        if (!empty($updates["message"])) {
            $fh = fopen("request.txt", "a");
            fwrite($fh, json_encode($updates).",\r\n");
            fclose($fh);
            $command = "";
            if(!isset($updates["message"]["text"]))
                $is_send = false;
            else {
                $message = $updates["message"]["text"];
                $reply_to_message_id = $updates["message"]["chat"]["id"];
                $response = "";
                if ($message == "/start") {
                    $response = "Hello! I'm a bot. I'm here to help you to gabut maksimal.\n\n";
                    foreach(Command::ListCommands() as $key => $value) {
                        $response .= $key." - ".$value['deskripsi']."\n";
                    }
                    $is_send = true;
                }
                else {
                    $i = 0;
                    foreach(Command::ListCommands() as $key => $value) {
                        if (strpos(strtolower($message), strtolower($key)) !== false) {
                            $response = Command::ListActions()[$i];
                            $command = $key;
                            $is_send = true;
                            break;
                        }
                        $i++;
                    }
                }
            }

            if($is_send) {
                $data['reply_to_message_id'] = $reply_to_message_id;
                if(isset(Command::ListCommands()[$command]) && (Command::ListCommands()[$command]['type'] == 'image')) {
                    $data['photo'] = $response;
                    Util::sendPhoto($data);
                }
                else {
                    $data['text'] = $response;
                    Util::sendMessage($data);
                }
                echo response()->json([
                    'status' => 'ok',
                    'data' => $data,
                    'message' => 'Send success'
                ], 200);
            }
            else {
                echo response()->json([
                    'status' => 'ok',
                    'data' => null,
                    'message' => 'Nothing to send'
                ], 200);
            }
            
            
        }
    }

    public function index()
    {
        $this->telegramWebhook();
    }
}
