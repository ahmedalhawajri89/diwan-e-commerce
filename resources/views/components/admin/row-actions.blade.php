{{--
  A table row's actions: edit, and delete behind the confirmation dialog.
  Either can be left out.

  @param  string       $name         what the row is, for the labels and the question
  @param  string|null  $edit         the edit URL
  @param  string|null  $delete       the destroy URL
  @param  string|null  $consequence  what else goes with it, said before it goes
--}}
@props(['name', 'edit' => null, 'delete' => null, 'consequence' => null])

<div class="flex justify-end gap-1">
    {{ $slot }}

    @if($edit)
        <x-ui.button variant="quiet" :href="$edit" aria-label="{{ __('admin.action.edit') }}: {{ $name }}">
            <x-icon name="pencil" class="size-4" />
        </x-ui.button>
    @endif

    @if($delete)
        <form method="POST" action="{{ $delete }}" class="delete-form"
              data-confirm-message="{{ $name }} — {{ $consequence ? $consequence.' ' : '' }}{{ __('admin.action.confirm_delete') }}">
            @csrf
            @method('DELETE')
            <x-ui.button type="submit" variant="quiet" class="hover:text-danger!" aria-label="{{ __('admin.action.delete') }}: {{ $name }}">
                <x-icon name="trash" class="size-4" />
            </x-ui.button>
        </form>
    @endif
</div>
