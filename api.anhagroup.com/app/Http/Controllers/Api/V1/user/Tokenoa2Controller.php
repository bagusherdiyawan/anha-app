<?php 

namespace App\Http\Controllers\Api\V1\user;

use App\Http\Controllers\Controller;

use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class Tokenoa2Controller extends Controller{
    protected $messaging;

    function __construct(Factory $factory){
        $this->messaging = $factory->createMessaging();
    }

    function send(Request $request){
        $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'token' => 'required|string',
        ]);

        try {
            $message = CloudMessage::new()
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create($request->title, $request->body))
                ->withToken($request->token);

            $this->messaging->send($message);

            return response()->json(['message' => 'Notification sent successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    function index(){
        // specify the path to your application credentials
        // putenv('GOOGLE_APPLICATION_CREDENTIALS=/path/to/my/credentials.json');

        // define the scopes for your API call
        $scopes = ['https://www.googleapis.com/auth/drive.readonly'];

        // create middleware
        $middleware = ApplicationDefaultCredentials::getMiddleware($scopes);
        $stack = HandlerStack::create();
        $stack->push($middleware);

        // create the HTTP client
        $client = new Client([
            'handler' => $stack,
            'base_uri' => 'https://www.googleapis.com',
            'auth' => 'google_auth'  
        // authorize all requests
        ]);

        // make the request
        $response = $client->get('drive/v2/files');

        // show the result!
        print_r((string) $response->getBody());
    }

}