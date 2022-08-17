<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use Validator;
use Config;
use DateTime;
use File;
use GuzzleHttp\Client;
use Socialite;

class OktaController extends Controller
{
    
    public function __construct()
	{
        date_default_timezone_set(Config::get('app.timezone'));

	}//END __construct

	public function getDatabaseFullText($searchText = "a")
	{
		/*$dataTest = DB::select(DB::raw("SELECT id,title,content,author, MATCH (title,content,author) AGAINST ('traveling to parks') as score FROM news WHERE MATCH (title,content,author) AGAINST ('traveling to parks') > 0 ORDER BY score DESC"));

		print_r($dataTest);*/
		DB::enableQueryLog();
		$dataFAQ = DB::select(DB::raw("SELECT id, user_skill_id, question, answer, MATCH (question, answer) AGAINST ('$searchText') as score FROM usf_que_ans WHERE MATCH (question, answer) AGAINST ('$searchText') > 0 ORDER BY score DESC"));
		print_r(DB::getQueryLog());
		print_r($dataFAQ);
		exit;

	}

	public function redirectToProvider()
	{
		return Socialite::driver('okta')->redirect();
		exit;

	}

	public function handleProviderCallback(Request $request)
	{
		print_r($request->input('id_token'));

        $user = Socialite::driver('okta')->user();

        var_dump( $user );
        var_dump($user->email);
        var_dump($user->name);
        var_dump($user->token);
        var_dump($user->first_name);
        var_dump($user->last_name);
        exit;
        $localUser = User::where('email', $user->email)->first();
        // create a local user with the email and token from Okta
        if (! $localUser) {
            $localUser = User::create([
                'email' => $user->email,
                'name'  => $user->name,
                'token' => $user->token,
            ]);
        } else {

            // if the user already exists, just update the token:
            $localUser->token = $user->token;
            $localUser->save();
        }

        try {
            Auth::login($localUser);
        } catch (\Throwable $e) {
            return redirect('/login-okta');
        }

        return redirect('/home');

	}

}
