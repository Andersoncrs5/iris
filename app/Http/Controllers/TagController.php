<?php

namespace App\Http\Controllers;

use App\DTOs\Tag\CreateTagDTO;
use App\DTOs\Tag\FilterTagDTO;
use App\DTOs\Tag\UpdateTagDTO;
use App\Http\Requests\Tag\CreateTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Services\Tag\CreateTagUseCase;
use App\Services\Tag\DeleteTagUseCase;
use App\Services\Tag\FindTagByIdUseCase;
use App\Services\Tag\ListTagUseCase;
use App\Services\Tag\ToggleTagUseCase;
use App\Services\Tag\UpdateTagUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Route;

class TagController
{

    public function __construct(
        private readonly CreateTagUseCase $createTagUseCase,
        private readonly ListTagUseCase $listTagUseCase,
        private readonly ToggleTagUseCase $toggleTagUseCase,
        private readonly DeleteTagUseCase $deleteTagUseCase,
        private readonly FindTagByIdUseCase $findTagByIdUseCase,
        private readonly UpdateTagUseCase $updateTagUseCase,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('tag.list');

        $filterDto = FilterTagDTO::fromRequest($request);
        $tags = $this->listTagUseCase->execute($filterDto);

        return view('tag.list', [
            'tags' => $tags,
        ]);
    }

    public function Create() 
    {
        Gate::authorize('tag.create');

        return view("tag.create");
    }
   
    public function Store(CreateTagRequest $request) 
    {
        Gate::authorize('tag.create');

        $dto = CreateTagDTO::fromRequest($request);

        $created = $this->createTagUseCase->execute($dto);

        if ($created->isFailure()) {
            return redirect()
                ->route('tag.list')
                ->with('error', $created->getError());
        }

        return redirect()
            ->route('tag.list')
            ->with('success', 'Tag created with success!');
    }

    public function edit(int $id)
    {
        Gate::authorize("tag.update");

        $result = $this->findTagByIdUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('tag.list')
                ->with('error', $result->getError());
        }

        $tag = $result->getValue();

        return view("tag.edit", compact("tag"));
    }

    public function update(UpdateTagRequest $request, int|string $id)
    {
        Gate::authorize('tag.update');

        $dto = UpdateTagDTO::fromRequest($request, $id);

        $result = $this->updateTagUseCase->execute($dto, $id);

        if ($result->isFailure()) {
            return redirect()->back()->with('error', $result->getError());
        }

        return redirect()
            ->route(Route::has('tag.index') ? 'tag.index' : 'tag.list')
            ->with('success', 'Tag updated with success!');
    }

    public function toggleActive(int $id)
    {
        Gate::authorize('tag.update');

        $result = $this->toggleTagUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('tag.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('tag.list')
            ->with('success', 'Tag updated with success!');
    }

    public function destroy(int $id)
    {
        Gate::authorize('tag.delete');

        $result = $this->deleteTagUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('tag.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('tag.list')
            ->with('success', 'Tag deleted with success!');
    }
   

}