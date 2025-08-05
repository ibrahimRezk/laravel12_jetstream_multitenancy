<script setup>
import AppLayout from "@/layouts/AppLayout.vue";
import { Link, Head, router } from "@inertiajs/vue3";
import PlaceholderPattern from "../components/PlaceholderPattern.vue";
import AddEditTenant from "./AddEditTenant.vue";
import { MoreHorizontal } from "lucide-vue-next";

import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from "@tanstack/vue-table";

import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/pagination";

import { ArrowUpDown, ChevronDown } from "lucide-vue-next";
import { computed, h, ref, toRef } from "vue";
import { cn, valueUpdater } from "@/lib/utils";

import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from "@/components/ui/dropdown-menu";
import { Input } from "@/components/ui/input";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
// import DropdownAction from './DataTableDemoColumn.vue'
import DropdownAction from "@/components/dataTableDropDown.vue";
import { watch } from "vue";

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";

const props = defineProps({
    tenants: {
        type: Object,
        default: () => {},
    },
    // subscriptions: {
    //     type: Array,
    //     default: () => [],
    // },
    plans: {
        type: Array,
        default: () => [],
    },
    // type: {
    //     type: String,
    //     default: () => null,
    // },

    errors: {
        type: Object,
        default: () => {},
    },
});

const breadcrumbs = [
    {
        title: "all Tenants",
        href: "/admin/tenants",
    },
];

const tenants = toRef(props, "tenants");
// const data = computed(()=>  tenants.value.data)

// const data = [
//   {
//     id: 'm5gr84i9',
//     amount: 316,
//     status: 'success',
//     email: 'ken99@yahoo.com',
//   },
//   {
//     id: '3u1reuv4',
//     amount: 242,
//     status: 'success',
//     email: 'Abe45@gmail.com',
//   },
//   {
//     id: 'derv1ws0',
//     amount: 837,
//     status: 'processing',
//     email: 'Monserrat44@gmail.com',
//   },
//   {
//     id: '5kma53ae',
//     amount: 874,
//     status: 'success',
//     email: 'Silas22@gmail.com',
//   },
//   {
//     id: 'bhqecj4p',
//     amount: 721,
//     status: 'failed',
//     email: 'carmella@hotmail.com',
//   },
// ]

const columns = [
    // no need for select all
    // {
    //     id: "select", // this  for show specefic items or hid them from top right column menu
    //     header: ({ table }) =>
    //         h(Checkbox, {
    //             modelValue:
    //                 table.getIsAllPageRowsSelected() ||
    //                 (table.getIsSomePageRowsSelected() && "indeterminate"),
    //             "onUpdate:modelValue": (value) =>
    //                 table.toggleAllPageRowsSelected(!!value),
    //             ariaLabel: "Select all",
    //         }),
    //     cell: ({ row }) =>
    //         h(Checkbox, {
    //             modelValue: row.getIsSelected(),
    //             "onUpdate:modelValue": (value) => row.toggleSelected(!!value),
    //             ariaLabel: "Select row",
    //         }),
    //     enableSorting: false,
    //     enableHiding: false,
    // },
    {
        accessorKey: "#",
        header: "#",
        cell: ({ row }) => h("div", { class: "capitalize" }, row.index + 1),
    },
    {
        accessorKey: "name",
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: "ghost",
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === "asc"),
                },
                () => ["name", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })]
            );
        },
        cell: ({ row }) => h("div", { class: "lowercase" }, row.original.name),
    },
    {
        accessorKey: "email",
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: "ghost",
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === "asc"),
                },
                () => ["email", h(ArrowUpDown, { class: "ml-2 h-4 w-4" })]
            );
        },
        cell: ({ row }) => h("div", { class: "lowercase" }, row.original.email),
    },

    {
        accessorKey: "subscription status",
        header: "Subscription Status",
        cell: ({ row }) =>
            h("div", { class: "capitalize" }, row.original.status),
    },
    {
        accessorKey: "plan",
        header: "plan",
        cell: ({ row }) => h("div", { class: "capitalize" }, row.original.plan),
    },
    {
        accessorKey: "interval",
        header: "interval",
        cell: ({ row }) =>
            h("div", { class: "capitalize" }, row.original.interval),
    },
    {
        accessorKey: "price",
        header: "price",
        cell: ({ row }) =>
            h("div", { class: "capitalize" }, row.original.price),
    },
    {
        accessorKey: "created at",
        header: "Created at",
        cell: ({ row }) =>
            h("div", { class: "capitalize" }, row.original.created_at),
    },
    {
        accessorKey: "ends at",
        header: "Ends at",
        cell: ({ row }) =>
            h("div", { class: "capitalize" }, row.original.ends_at),
    },
    {
        accessorKey: "trial ends at",
        header: "Trial Ends At",
        cell: ({ row }) =>
            h("div", { class: "capitalize" }, row.original.trial_ends_at),
    },

    //   {
    //     accessorKey: 'amount',
    //     header: () => h('div', { class: 'text-right' }, 'Amount'),
    //     cell: ({ row }) => {
    //       const amount = Number.parseFloat(row.getValue('amount'))

    //       // Format the amount as a dollar amount
    //       const formatted = new Intl.NumberFormat('en-US', {
    //         style: 'currency',
    //         currency: 'USD',
    //       }).format(amount)

    //       return h('div', { class: 'text-right font-medium' }, formatted)
    //     },
    //   },
    {
        id: "actions",
        enableHiding: false,
        cell: ({ row }) => {
            const actions = [
                {
                    name: "edit subscription",
                    action: "editSubscription",
                    show: true,
                },
                {
                    name: "cancel subscription",
                    action: "cancelSubscription",
                    show: row.original.plan
                        ? row.original.status != "canceled"
                        : false,
                },
            ];

            return h(DropdownAction, {
                actions,
                onExpand: row.toggleExpanded,
                row: row.original,
                // showTriger: row.original.subscriptions.at(-1).status != 'canceled',
                // showTriger: row.original.currentSubscription?.status != 'canceled',
                onEditSubscription: (rowData) =>
                    handleEditSubscription(rowData),
                onCancelSubscription: (rowData) =>
                    handleCancelSubscription(rowData),
            });
        },
    },
];
const isDialogOpen = ref(false);
// const handleGetTenantSubscription = (row) => {
//     plan.value = row
//     isDialogOpen.value = true
//     action.value = 'view'

// }
const handleEditSubscription = (row) => {
    tenant.value = row;
    isDialogOpen.value = true;
    action.value = "edit";
};

const isAlertDialogOpen = ref(false);
const handleCancelSubscription = (row) => {
    isAlertDialogOpen.value = true;
    itemsToBeDeleted.value = row.id; // tenant id
};
const action = ref("");
const tenant = ref({});

const sorting = ref([]);
const columnFilters = ref([]);
const columnVisibility = ref({});
const rowSelection = ref({});
const expanded = ref({});

const table = useVueTable({
    get data() {
        return tenants.value.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, expanded),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
        get expanded() {
            return expanded.value;
        },
    },
});

watch(
    () => rowSelection.value,
    () => checkAllItems()
);

const onPageChange = (page) => {
    router.get(route("admin.tenants", { page: page }));
};

const checkedItems = ref([]);
const checkAllItems = () => {
    checkedItems.value = [];

    Object.keys(rowSelection.value).forEach((key) =>
        checkedItems.value.push(tenants.value.data[key]["id"])
    );

    // itemsToBeDeleted.value = checkedItems.value;
    // checkedItems.value.length > 0
    //     ? (showAlertModal.value = true)
    //     : (showAlertModal.value = false);
};

const itemsToBeDeleted = ref({});

const cancelTenants = () => {
    router.delete(
        route("admin.cancelSubscription", {
            tenantIds: itemsToBeDeleted.value,
            // tenantIds: checkedItems.value,
        }),
        {
            preserveScroll: true,
            preserveState: true,
            onBefore: () => {
                // isDeleting.value = true;
            },
            onSuccess: () => {
                // deleteModal.value = false;
                // itemToDelete.value = [];
                // ids.value = [];
                // deleteMultipleItems.value = false;
            },
            onFinish: () => {
                // isDeleting.value = false;
                // usePage().props.menus.forEach((menu) => {
                //     menu.isActive
                //         ? (menu.open = true)
                //         : (menu.open = false);
                // });
            },
        }
    );
};

const showAlertModal = ref(false);

// watch(
//     () => deleteMultipleItems.value,
//     () => (deleteMultipleItems.value == false ? (checkedItems.value = []) : "")
// );

// const deleteAll = () => {
//     deleteMultipleItems.value = true;
//     showDeleteModal(checkedItems.value);
// };
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="w-full p-2 rounded bg-background border">
                <div class="flex gap-2 items-center py-4">
                    <AddEditTenant
                        :plans="props.plans"
                        :item="tenant"
                        :isDialogOpen
                        @close="isDialogOpen = false"
                        :action
                    />
                    <Input
                        class="max-w-sm"
                        placeholder="Filter tenant name..."
                        :model-value="table.getColumn('name')?.getFilterValue()"
                        @update:model-value="
                            table.getColumn('name')?.setFilterValue($event)
                        "
                    />

                    <AlertDialog v-model:open="isAlertDialogOpen">
                        <AlertDialogTrigger as-child v-show="showAlertModal">
                            <Button variant="destructive">
                                cancel all selected
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle
                                    >Are you absolutely sure?</AlertDialogTitle
                                >
                                <AlertDialogDescription>
                                    This action cannot be undone. This will
                                    permanently delete your account and remove
                                    your data from our servers.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction @click="cancelTenants"
                                    >Continue</AlertDialogAction
                                >
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>

                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="ml-auto">
                                Columns <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuCheckboxItem
                                v-for="column in table
                                    .getAllColumns()
                                    .filter((column) => column.getCanHide())"
                                :key="column.id"
                                class="capitalize"
                                :model-value="column.getIsVisible()"
                                @update:model-value="
                                    (value) => {
                                        column.toggleVisibility(!!value);
                                    }
                                "
                            >
                                {{ column.id }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div class="rounded-md border">
                    <Table>
                        <TableHeader>
                            <TableRow
                                v-for="headerGroup in table.getHeaderGroups()"
                                :key="headerGroup.id"
                            >
                                <TableHead
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                >
                                    <FlexRender
                                        v-if="!header.isPlaceholder"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="table.getRowModel().rows?.length">
                                <template
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                >
                                    <TableRow
                                        :data-state="
                                            row.getIsSelected() && 'selected'
                                        "
                                    >
                                        <TableCell
                                            v-for="cell in row.getVisibleCells()"
                                            :key="cell.id"
                                        >
                                            <FlexRender
                                                :render="
                                                    cell.column.columnDef.cell
                                                "
                                                :props="cell.getContext()"
                                            />
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="row.getIsExpanded()">
                                        <TableCell
                                            :colspan="row.getAllCells().length"
                                        >
                                            {{ JSON.stringify(row.original) }}
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </template>

                            <TableRow v-else>
                                <TableCell
                                    :colspan="columns.length"
                                    class="h-24 text-center"
                                >
                                    No results.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex items-center justify-end space-x-2 py-4">
                    <div class="flex-1 text-sm text-muted-foreground">
                        {{ table.getFilteredSelectedRowModel().rows.length }} of
                        {{ table.getFilteredRowModel().rows.length }} row(s)
                        selected.
                    </div>
                    <!-- <div class="space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            Next
                        </Button>
                    </div> -->
                </div>

                <Pagination
                    v-slot="{ page }"
                    :items-per-page="props.tenants.meta.per_page"
                    :total="props.tenants.meta.total"
                    :default-page="props.tenants.meta.current_page"
                    @update:page="onPageChange"
                >
                    <PaginationContent v-slot="{ items }">
                        <PaginationPrevious />

                        <template v-for="(item, index) in items" :key="index">
                            <PaginationItem
                                v-if="item.type === 'page'"
                                :value="item.value"
                                :is-active="item.value === page"
                            >
                                {{ item.value }}
                            </PaginationItem>
                        </template>

                        <PaginationEllipsis :index="4" />

                        <PaginationNext />
                    </PaginationContent>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
