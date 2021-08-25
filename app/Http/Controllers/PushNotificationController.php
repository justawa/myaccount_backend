<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Edujugon\PushNotification\Facades\PushNotification;
use App\FirebaseToken;

class PushNotificationController extends Controller
{

    public function show_notification_form()
    {
        return view('admin.notification.show_notification_form');
    }

    public function sendFCMPushNotification(Request $request)
    {

        // return $request->all();
        $this->validate($request, [
            'title' => 'required',
            'body' => 'required'
        ]);

        $firebaseData = FirebaseToken::all();
        $firebaseTokens = array();

        foreach( $firebaseData as $firebase ){
            $firebaseTokens[] = $firebase->token;
        }

        $status = PushNotification::setService('fcm') // change as per your requirement FCM / GCM / APN
            ->setMessage([
                'notification' => [
                    'title' => $request->title,
                    'body' => $request->body,
                    'sound' => 'default',
                    'click_action' => 'OPEN_ACTIVITY_1'
                ]
            ])
            ->setApiKey( \Config::get('pushnotification.fcm.apiKey') ) // only if you are using GCM/FCM
            ->setDevicesToken( $firebaseTokens )
            ->send()
            ->getFeedback();

        if( $status->success ) {
            return redirect()->back()->with('success', 'Message sent successfully');
        } else {
            return redirect()->back()->with('failure', 'Failed to send Message')->withInput();
        }
    }
}
