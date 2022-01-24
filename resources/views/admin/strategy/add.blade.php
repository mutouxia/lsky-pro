<x-app-layout>
    <div class="my-6 md:my-10">
        <div class="md:mt-0 md:col-span-2">
            <form action="{{ route('admin.group.create') }}" method="POST">
                <div class="overflow-hidden rounded-md">
                    <div class="px-4 py-5 bg-white sm:p-6 space-y-4">

                        <div class="col-span-6">
                            <label for="name" class="block text-sm font-medium text-gray-700"><span class="text-red-600">*</span>名称</label>
                            <input type="text" name="name" id="name" placeholder="请输入策略名称" autocomplete="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <div class="col-span-6">
                            <label for="intro" class="block text-sm font-medium text-gray-700">简介</label>
                            <textarea id="intro" name="intro" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md" placeholder="请输入简介，可为空"></textarea>
                        </div>

                        <div class="col-span-6 sm:col-span-3">
                            <label for="key" class="block text-sm font-medium text-gray-700">储存策略</label>
                            <select id="key" name="key" autocomplete="key" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @foreach(\App\Models\Strategy::DRIVERS as $key => $driver)
                                <option value="{{ $key }}" {{ $loop->first ? 'selected' : '' }}>{{ $driver }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-6">
                            <div class="mb-4 hidden" data-driver="{{ \App\Enums\StrategyKey::Local }}">
                                <div class="col-span-6 sm:col-span-3 mb-4">
                                    <label for="configs[domain]" class="block text-sm font-medium text-gray-700"><span class="text-red-600">*</span>访问域名</label>
                                    <input type="text" name="configs[domain]" id="configs[domain]" autocomplete="text" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" placeholder="请输入图片访问域名">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <x-button type="button" class="bg-gray-500" onclick="history.go(-1)">取消</x-button>
                        <x-button>确认创建</x-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            // 设置选中驱动
            let setSelected = function () {
                $('[data-driver]').each(function () {
                    $(this)[$('#key').val() == $(this).data('driver') ? 'show' : 'hide']();
                });
            };

            setSelected();

            $('#key').change(function () {
                setSelected();
            });

            $('form').submit(function (e) {
                e.preventDefault();
                axios.post(this.action, $(this).serialize()).then(response => {
                    if (response.data.status) {
                        toastr.success(response.data.message);
                        setTimeout(function () {
                            window.location.href = '{{ route('admin.strategies') }}';
                        }, 3000);
                    } else {
                        toastr.error(response.data.message);
                    }
                });
            });
        </script>
    @endpush

</x-app-layout>
