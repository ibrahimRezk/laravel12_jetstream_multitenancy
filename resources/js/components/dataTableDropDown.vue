<script setup>
import { MoreHorizontal } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";

defineProps({
    row: {
        type: Object,
        required: true,
    },
    showTriger: {
        type: Boolean,
        default: true,
    },
    actions: {
        type: Object,
        required: true,
        validator: (value) => {
            return value && typeof value === "object";
        },
    },
});
// defineProps({
//     payment: {
//         type: Object,
//         required: true,
//         validator: (value) => {
//             return value && typeof value.id === "string";
//         },
//     },
// });

// function copy(id) {
//     navigator.clipboard.writeText(id);
// }
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child v-show="showTriger">
            <Button variant="ghost" class="w-8 h-8 p-0">
                <span class="sr-only">Open menu</span>
                <MoreHorizontal class="w-4 h-4" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end">
            <DropdownMenuLabel>Actions</DropdownMenuLabel>
            <DropdownMenuSeparator />

            <DropdownMenuItem
            v-for="(value, key, index) in actions"
            :key="index"
            @click="$emit(value.action, row)"
            v-show="value.show"
            >
                {{ value.name }}
            </DropdownMenuItem>

            <!-- <DropdownMenuItem @click="copy(payment.id)"> Copy payment ID </DropdownMenuItem>
            <DropdownMenuItem>View customer</DropdownMenuItem>
            <DropdownMenuItem>View payment details</DropdownMenuItem> -->
        </DropdownMenuContent>
    </DropdownMenu>
</template>
