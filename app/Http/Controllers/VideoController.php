<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::paginate(10);
        return response()->json($videos);
    }
    public function allVideos() {
        $videos = Video::all(); // Replace with your actual method to fetch all videos
        return response()->json($videos);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'videos.*' => 'required|mimes:mp4|max:10240',
            'images.*' => 'required|mimes:jpeg,jpg,png|max:2048',
        ]);

        $lastVideo = Video::latest('id')->first();
        $lastId = $lastVideo ? $lastVideo->id : 0;

        $videos = $request->file('videos');
        $images = $request->file('images');

        $uploadedVideos = [];
        foreach ($videos as $index => $video) {
            $newId = $lastId + $index + 1;
            $videoPath = "video/{$newId}.mp4";
            $imagePath = "image/{$newId}.jpg";

            Storage::disk('s3')->put($videoPath, fopen($video, 'r+'), 'public');
            Storage::disk('s3')->put($imagePath, fopen($images[$index], 'r+'), 'public');

            $videoModel = Video::create([
                'title' => "Video {$newId}",
                'video_path' => $videoPath,
                'thumbnail_path' => $imagePath,
                'classid' => $request->input('classid', 1),
            ]);

            $uploadedVideos[] = $videoModel;
        }

        return response()->json($uploadedVideos);
    }

    public function destroy(Request $request)
    {
        $ids = $request->input('ids');
        $videos = Video::whereIn('id', $ids)->get();

        foreach ($videos as $video) {
            Storage::disk('s3')->delete($video->video_path);
            Storage::disk('s3')->delete($video->thumbnail_path);
            $video->delete();
        }

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function updateClass(Request $request, $id)
    {
        //$video = Video::find($id);
        //return $video;
        //$video->classid = $request->input('classid');
        //$video->save();

        //return response()->json($video);
        $classid = request()->query('classid');

        // Validate the classid, ensure it's a valid class
        // You might want to add validation logic here

        // Find the video by id and update the classid
        $video = Video::find($id);
        if ($video) {
            $video->classid = $classid;
            $video->save();
            return response()->json(['message' => 'Class updated successfully'], 200);
        }

        return response()->json(['message' => 'Video not found'], 404);
    }
    public function extractLastNumber($url) {
    // Use a regular expression to match the last number in the URL
        if (preg_match('/(\d+)(?=\.\w+$)/', $url, $matches)) {
             return $matches[1];
        }
        return null; // Return null if no number is found
    }
    public function updateThumbnail(Request $request)
    {
        //$thumbName = $this->extractLastNumber($request->thumb).".jpg";
        // Extract last number and form the new thumbnail name
        $thumbName = $this->extractLastNumber($request->thumb) . ".jpg";

        $request->validate([
                'thumbnail' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240', // 10MB limit
        ]);

        if ($request->hasFile('thumbnail')) {
            // Define the S3 path with the extracted thumbName
            $path = '/image/' . $thumbName;

            // Check if the file already exists in S3
            if (Storage::disk('s3')->exists($path)) {
                // Delete the existing file
                //return $path;
               Storage::disk('s3')->delete($path);
            }

            // Store the new thumbnail on S3
            Storage::disk('s3')->put($path, file_get_contents($request->file('thumbnail')), 'public');

            return response()->json(['message' => 'Thumbnail updated successfully', 'thumbnail' => $path], 200);
        }

        return response()->json(['message' => 'No thumbnail uploaded'], 400);
    }
}
