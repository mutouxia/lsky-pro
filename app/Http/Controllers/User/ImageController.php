<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Album;
use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ImageController extends Controller
{
    public function index(): View
    {
        return view('images');
    }

    public function images(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();

        $images = $user->images()->when($request->query('order') ?: 'newest', function (Builder $builder, $order) {
            switch ($order) {
                case 'earliest':
                    $builder->orderBy('created_at');
                    break;
                case 'utmost':
                    $builder->orderByDesc('size');
                    break;
                case 'least':
                    $builder->orderBy('size');
                    break;
                default:
                    $builder->latest();
            }
        })->when($request->query('keyword'), function (Builder $builder, $keyword) {
            $builder->whereRaw("concat(IFNULL(origin_name,''),IFNULL(alias_name,'')) like ?", ["%{$keyword}%"]);
        })->when((int) $request->query('album_id'), function (Builder $builder, $albumId) {
            $builder->where('album_id', $albumId);
        }, function (Builder $builder) {
            $builder->whereNull('album_id');
        })->paginate(40);
        $images->getCollection()->each(function (Image $image) {
            $image->human_date = $image->created_at->diffForHumans();
            $image->date = $image->created_at->format('Y-m-d H:i:s');
            $image->append(['url', 'filename', 'links'])->setVisible([
                'id', 'filename', 'url', 'human_date', 'date', 'human_date', 'width', 'height', 'links'
            ]);
        });
        return $this->success('success', compact('images'));
    }

    public function movement(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        DB::transaction(function () use ($user, $request) {
            /** @var null|Album $album */
            $album = $user->albums()->find((int) $request->input('id'));
            $user->images()->whereIn('id', $request->input('selected'))->update([
                'album_id' => $album->id ?? null,
            ]);
            if ($album) {
                $album->image_num = $album->images()->count();
                $album->save();
            }
            if ($albumId = (int) $request->input('album_id')) {
                /** @var Album $originAlbum */
                $originAlbum = $user->albums()->find($albumId);
                $originAlbum->image_num = $originAlbum->images()->count();
                $originAlbum->save();
            }
        });
        return $this->success('移动成功');
    }

    public function delete(Request $request): Response
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Image $image */
        foreach ($user->images()->with('strategy')->whereIn('id', $request->all() ?: [])->cursor() as $image) {
            $image->delete();
        }
        return $this->success('删除成功');
    }
}
