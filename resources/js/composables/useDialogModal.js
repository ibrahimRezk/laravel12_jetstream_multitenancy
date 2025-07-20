import { ref } from "vue";
import { trans } from "laravel-vue-i18n";
import { router } from "@inertiajs/vue3";
import { usePage } from "@inertiajs/vue3";

export default function (params) {
    const {
        routeResourceName: theRouteResourceName,
        form: formItems,
        opened: openedMenu,
        showScreenExeptSubmenu,
        method: calledMethod,
        editMode: editModeValue,
        withImage: withImageValue,
    } = params;
    const form = ref(formItems);
    const opened = ref(openedMenu);
    const method = ref(calledMethod);
    const routeResourceName = ref(theRouteResourceName);
    const editMode = ref(editModeValue);
    const withImage = ref(withImageValue);
    const dialogModal = ref(false);
    const secondDialogModal = ref(false);
    const thirdDialogModal = ref(false);
    const fourthDialogModal = ref(false);
    const fifthDialogModal = ref(false);
    const sixthDialogModal = ref(false);
    const seventhDialogModal = ref(false);
    const eighthDialogModal = ref(false);
    const itemToSave = ref({});
    const isSaving = ref(false);

    const current_lang = document
        .getElementsByTagName("html")[0]
        .getAttribute("lang");

    function closeDialogModal() {
        dialogModal.value = false;
        secondDialogModal.value = false;
        thirdDialogModal.value = false;
        fourthDialogModal.value = false;
        fifthDialogModal.value = false;
        sixthDialogModal.value = false;
        seventhDialogModal.value = false;
        eighthDialogModal.value = false;
        editMode.value = false;
        itemToSave.value = {};
        opened.value = 0;
        showScreenExeptSubmenu.value = false;
    }

    function showDialogModal() {
        editMode.value = false;
        dialogModal.value = true;
        form.value.reset();
    }

    function showEditModal(item) {
        dialogModal.value = true;
        itemToSave.value = item;
    }

    // here we use router.post not form.post because we send extra data and we check for errors with another way  with props.errors
    // important   ,in update  we put route with id in classroom 1 () and data in classroom 2 {} and options in classroom 3
    function handleSavingItem() {
        if (editMode.value == true) {
            if (withImage.value == true) {
                form.value.post(
                    //   route(`${props.routeResourceName}.update`, {  // not working with multipart/formData   can not update files or images
                    route(`${routeResourceName.value}.${method.value}`, {
                        _method: "put", // we use it like this and modify route to be post not put and re enter _mothod :put because when uploading files like images it is not supported in inetia  ,, remember to create new put route in routes
                        id: itemToSave.value.id,
                    }),
                    {
                        preserveScroll: true,
                        preserveState: true,
                        onBefore: () => {
                            isSaving.value = true;
                        },
                        onSuccess: () => {
                            // form.reset()   /// try it
                            isSaving.value = false;

                            closeDialogModal();
                            // Toast.fire({
                            //     position: current_lang == 'ar'? 'top-start' : 'top-end',
                            //     icon: "success",
                            //     title: trans('general.item updated successfully'),
                            //     iconColor: "white",
                            //     color: "black", // text color
                            //     background: "#B8860B        ", // gold

                            //     // background: "#6699ff", // blue
                            //     // background: '#00a877       ', // green
                            //     // background: '#39ff14   ', // lime
                            //     // background: '#dc143c    ', // red
                            // });
                        },
                        onFinish: () => {
                            isSaving.value = false;
                            usePage().props.menus.forEach((menu) => {
                                menu.isActive
                                    ? (menu.open = true)
                                    : (menu.open = false);
                            });
                        },
                    },
                    {
                        preserveState: true,
                    }
                );
            } else {
                router.put(
                    route(`${routeResourceName.value}.${method.value}`, {
                        id: itemToSave.value.id,
                    }),
                    {
                        ...form.value,
                        id: itemToSave.value.id,
                        // data: itemToSave.value,
                    },
                    {
                        preserveScroll: true,
                        preserveState: true,
                        onBefore: () => {
                            isSaving.value = true;
                        },
                        onSuccess: () => {
                            // form.reset()   /// try it
                            isSaving.value = false;
                            closeDialogModal();
                            // Toast.fire({
                            //     position: current_lang == 'ar'? 'top-start' : 'top-end',
                            //     icon: "success",
                            //     title: trans('general.item updated successfully'),
                            //     iconColor: "white",
                            //     color: "black", // text color
                            //     background: "#B8860B        ", // gold

                            //     // background: "#6699ff", // blue        ', // green
                            //     // background: '#00a877       ', // green
                            //     // background: '#39ff14   ', // lime
                            //     // background: '#dc143c    ', // red
                            // });
                        },
                        onFinish: () => {
                            isSaving.value = false;
                            usePage().props.menus.forEach((menu) => {
                                menu.isActive
                                    ? (menu.open = true)
                                    : (menu.open = false);
                            });
                        },
                    }
                );
            }
        } else {
            router.post(
                route(`${routeResourceName.value}.${method.value}`),
                {
                    ...form.value,
                },
                {
                    preserveScroll: true,
                    preserveState: true,
                    onBefore: () => {
                        isSaving.value = true;
                    },
                    onSuccess: () => {
                        isSaving.value = false;
                        closeDialogModal();
                        // Toast.fire({
                        //     position: current_lang == 'ar'? 'top-start' : 'top-end',
                        //     icon: "success",
                        //     title: trans('general.item created successfully'),
                        //     iconColor: "white",
                        //     color: "black", // text color
                        //     // background: "#6699ff        ", // blue
                        //     background: "#B8860B        ", // gold
                        //     // background: "#4169E1        ", // blue
                        //     // background: '#00a877       ', // green
                        //     // background: '#39ff14   ', // lime
                        //     // background: '#dc143c    ', // red
                        // });
                    },
                    onFinish: () => {
                        isSaving.value = false;
                        usePage().props.menus.forEach((menu) => {
                            menu.isActive
                                ? (menu.open = true)
                                : (menu.open = false);
                        });
                    },
                    onError: () => {
                        // Toast.fire({
                        //                 position: current_lang == 'ar'? 'top-start' : 'top-end',
                        //                 icon: "error",
                        //                 title: trans('general.no data available'),
                        //                 iconColor: "white",
                        //                 color: "black", // text color
                        //                 // background: "#6699ff", // blue        ', // green
                        //                 // background: '#00a877       ', // green
                        //                 // background: '#39ff14   ', // lime
                        //                 background: '#dc143c    ', // red
                        //             });
                    },
                }
            );
        }
    }

    function handleGetData() {
        router.get(
            route(`${routeResourceName.value}.${method.value}`),
            {
                ...form.value,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onBefore: () => {
                    isSaving.value = true;
                },
                onSuccess: () => {
                    closeDialogModal();
                },
                onFinish: () => {
                    isSaving.value = false;
                    usePage().props.menus.forEach((menu) => {
                        menu.isActive
                            ? (menu.open = true)
                            : (menu.open = false);
                    });
                },
                onError: () => {
                    // Toast.fire({
                    //                 position: current_lang == 'ar'? 'top-start' : 'top-end',
                    //                 icon: "error",
                    //                 title: trans('general.no data available'),
                    //                 iconColor: "white",
                    //                 color: "black", // text color
                    //                 background: '#dc143c    ', // red
                    //             });
                },
            }
        );
    }

    return {
        closeDialogModal,
        dialogModal,
        secondDialogModal,
        thirdDialogModal,
        fourthDialogModal,
        fifthDialogModal,
        sixthDialogModal,
        seventhDialogModal,
        eighthDialogModal,
        itemToSave,
        isSaving,
        showDialogModal,
        showEditModal,
        handleSavingItem,
        handleGetData,
    };
}
