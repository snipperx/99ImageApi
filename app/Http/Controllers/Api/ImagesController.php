<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Images;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\ImagesResource;
use Kreait\Firebase\Auth as FirebaseAuth;
use Google\Cloud\Firestore\FirestoreClient;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Auth\SignInResult\SignInResult;
use PhpParser\Node\Stmt\Foreach_;

class ImagesController extends Controller {
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */

    public function get_data() {

        return ImagesResource::collection(DB::table('users')
            ->select('images.*', 'users.*')
            ->join('images','users.id','=','images.user_id')
            ->where( 'users.optIn', 1 )
            ->get());
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store( Request $request ) {
        //
        $request->validate( [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ] );

        $imageName = hash( 'sha1', $request->image . random_int ( 100, 1000000 ) );

        $student   = app( 'firebase.firestore' )->database()->collection( 'Images' )->document( $imageName );
        $firebase_storage_path = 'Images/';
        $name     = $student->id();

        $localfolder = public_path( 'firebase-temp-uploads' ) .'/';
        $extension = $request->image->extension();

        $file      = $name. '.' . $extension;

        $images = array();

        $files =  $request->image;

        array_push( $images, $files );

        foreach ( $images as $imager ) {
            if ( $imager->move( $localfolder, $file ) ) {
                $uploadedfile = fopen( $localfolder.$file, 'r' );
                $resp  = app( 'firebase.storage' )->getBucket()->upload( $uploadedfile, [ 'name' => $firebase_storage_path . $file ] );
                //will remove from local laravel folder
                unlink( $localfolder . $file );

                // $file
                $urls =  $this->getLink( $file );

                Images::create( [
                    'name' => $imageName,
                    'path' =>  $urls,
                    'user_id' =>  Auth::id(),
                ] );
            }
        }

        if ( $resp ) {
            return response()->json( [
                'success' => 'Image Uploaded successfully' ], 200 );
            } else {
                return response()->json( [ 'error' => 'Not allowed' ], 405 );
            }

        }

        private function getLink( $url ) {

            $expiresAt = new \DateTime( 'tomorrow' );
            $imageReference = app( 'firebase.storage' )->getBucket()->object( 'Images/'. $url );

            if ( $imageReference->exists() ) {
                $image = $imageReference->signedUrl( $expiresAt );
            } else {
                $image = null;
            }

            return $image;
        }

        public function getImages(){

            return ImagesResource::collection(DB::table('users')
                ->select('images.*', 'users.*')
                ->join('images','users.id','=','images.user_id')
                ->where( 'users.optIn', 1 )
                ->get());

////
//              return  ImagesResource::collection(images);


    }

    }

