import { ref } from "vue";
import { router } from "@inertiajs/vue3";
// import { trans } from "laravel-vue-i18n";
import { usePage } from "@inertiajs/vue3";

export default function (params) {
    const { routeResourceName: theRouteResourceName, method: calledMethod } =
        params;

    const method = ref(calledMethod ?? "destroy");
    const routeResourceName = ref(theRouteResourceName);

    // const itemToDelete = ref({});
    const itemToDelete = ref([]);
    const deleteModal = ref(false);
    const deleteMultipleItems = ref(false);
    const isDeleting = ref(false);
    const ids = ref([]);

    const current_lang = document
        .getElementsByTagName("html")[0]
        .getAttribute("lang");

    function close() {
        deleteModal.value = false;
        itemToDelete.value = [];
        ids.value = [];
        deleteMultipleItems.value = false;
        // itemToDelete.value = {};
    }

    function showDeleteModal(item) {
        deleteModal.value = true;

        if (deleteMultipleItems.value == true) {
            itemToDelete.value = item;
            itemToDelete.value.forEach((i) => ids.value.push(i.id));
        } else {
            itemToDelete.value.push(item);
            ids.value.push(item.id);
        }
    }

    function handleDeleteItem() {
        // console.log(ids.value)
        router.delete(
            route(`${routeResourceName.value}.${method.value}`, {
                id: ids.value,
            }),
            {
                preserveScroll: true,
                preserveState: true,
                onBefore: () => {
                    isDeleting.value = true;
                },
                onSuccess: () => {
                    deleteModal.value = false;
                    itemToDelete.value = [];
                    ids.value = [];
                    deleteMultipleItems.value = false;

                    // itemToDelete.value = {};

                    // Toast.fire({
                    //   // toast: true,
                    //   position: current_lang == 'ar'? 'top-start' : 'top-end',
                    //   icon: "success",
                    //   title: method.value == 'destroy' ? trans('general.item deleted successfully') : trans('general.item rolled back successfully'),

                    //     iconColor: 'white',
                    //     color:'black',  // text color
                    //     // background: '#1cac78        ', // green
                    //     // background: '#00a877       ', // green
                    //     // background: '#39ff14   ', // lime
                    //     // background: '#dc143c    ', // red
                    //     // background: "#B8860B        ", // gold

                    //     background: method.value == 'destroy' ? '#dc143c' : '#B8860B', // red
                    // });
                },
                onFinish: () => {
                    isDeleting.value = false;
                    usePage().props.menus.forEach((menu) => {
                        menu.isActive
                            ? (menu.open = true)
                            : (menu.open = false);
                    });
                },
            }
        );
    }

    return {
        deleteModal,
        itemToDelete,
        isDeleting,
        showDeleteModal,
        deleteMultipleItems,
        handleDeleteItem,
        close,
    };
}
