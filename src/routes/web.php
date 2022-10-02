<?php
Route::group(['namespace'=>'Yarm\Servicetools\Http\Controllers','prefix'=>'dlbt','middleware'=>['web']], function (){

    // Get comments on illustrations
    Route::get('/commentsOnIllustrations', 'DataCleaningController@commentsOnIllustrationsList')
        ->name('comments_on_illustartions');
});
