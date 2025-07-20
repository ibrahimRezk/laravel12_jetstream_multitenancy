import { ref, watch, computed } from "vue";
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'

import { useGeneralStore } from '@/stores/general';
import { storeToRefs } from 'pinia';
// const useGeneral = useGeneralStore()
// const { paginationNumber } = storeToRefs(useGeneral)



export default function (params) {
    const { filters: defaultFilters, routeResourceName, method , id } = params;
    // we have to convert filters to ref becase it is calling a v-model
    const filters = ref(defaultFilters );

    ///// we can use it like below but after refresh the page we lose the input
    // const {  routeResourceName } = params;
    // const filters = ref({});
    function resetFilter() {
        // const searchParams = new URLSearchParams(window.location.search);
        // const paginationNumber = searchParams.get('paginationNumber')
        Object.keys(filters.value).forEach((k) =>  k !== 'paginationNumber' ? filters.value[k] = '' : '' )
        
        const useGeneral = useGeneralStore()
        const { paginationNumber } = storeToRefs(useGeneral)

        filters.value = ({paginationNumber : paginationNumber.value});
    }

    const isFilled = computed(() => {
        let { page, paginationNumber , ...rest } = filters.value; /// page is the first parameter in addressbar after ? "[http://127.0.0.1:8000/admin/products?page=1&name=]" it will be alwayes there and have value because of pagination so we want to look at the second or third parameter or whatever  witch is ...rest  and apply filters.value to it and if it is there it will return true otherwise it will return false //// we did that to return false after we remove search input and refresh
        return Object.values(rest).some(
            (v) => !["", null, undefined].includes(v) 
        );
    });

    const isLoading = ref(false);
    const fetchItemsHandler = ref(null);

    function fetchItems() {
        Object.values(filters.value).length == 1 && Object.keys(filters.value)[0] == 'page' ? filters.value = {} : ''
        let filtersWithPage = ref(Object.values(filters.value).length > 0 ?  {...filters.value , page:1} : '')

        router.get(
            route(`${routeResourceName}.${method}` , {id : id}),
            {
                ...filtersWithPage.value,
            },
            {
                preserveState: true,
                preserveScroll: true, 
                replace: true,
                onBefore: () => (isLoading.value = true),
                // onFinish: () => isLoading.value = false,
                onFinish: () => [
                    (isLoading.value = false),
                    usePage().props.menus.forEach((menu) => {
                        // menu.label.toLowerCase() == routeResourceName
                        //     ? (menu.open = true)
                        //     : (menu.open = false);
                        menu.isActive ? menu.open = true : menu.open = false
                    }),
                ],
            }
        );
    }

    

    watch(
        filters,
        () => {
            // console.log(filters.value)
            clearTimeout(fetchItemsHandler.value);

            // filters.value.forEach((f , i) => f == '' ? filters.value.unshift(i , 1) : '')
            fetchItemsHandler.value = setTimeout(() => {
                fetchItems();
            }, 300);
            // delete empty filter from url
            Object.keys(filters.value).forEach((f) => (filters.value[f] == '' && filters.value[f] !== 0) ? delete filters.value[f] : '')
        },
        {
            deep: true,
        }
    );

    return {
        filters,
        isLoading,
        isFilled,
        resetFilter,
    };
}
