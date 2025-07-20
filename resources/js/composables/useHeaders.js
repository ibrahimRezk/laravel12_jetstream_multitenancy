import { ref } from "vue";

export default function (params) {
    const {
        dbHeaders: theDbHeaders,
        headers: theFinalHeaders,
        prepairFilteredHeaders: theFilteredHeaders,
    } = params;

    const dbHeaders = ref(theDbHeaders);
    const finalHeaders = ref(theFinalHeaders);
    const filteredHeaders = ref(theFilteredHeaders);

    const filterHeadersMethod = () => {
        finalHeaders.value = [];
        dbHeaders.value.forEach((header) => {
            let head = filteredHeaders.value.find((h) => h.name == header.name);
            if (head !== undefined) {
                finalHeaders.value.push(head);
            }
        });
    };

    const showColumnItems = (name) => {
        let head = finalHeaders.value.find((h) => h.name == name);
        return head?.label === name;
    };

    return {
        filterHeadersMethod,
        showColumnItems,
        finalHeaders,
        filteredHeaders,
    };
}

//////////// original methods in index file

// const finalHeaders = ref(props.headers);
// const filteredHeaders = ref(props.headers);
// const allHeaders = () => {
//     finalHeaders.value = [];
//     props.headers.forEach((header) => {
//         let head = filteredHeaders.value.find((h) => h.name == header.name);
//         if (head !== undefined) {
//             finalHeaders.value.push(head);
//         }
//     });
// };

// const showColumnItems = (name) => {
//     let head = finalHeaders.value.find((h) => h.name == name);
//     return head?.label === name;
// };

// watch(
//     () => filteredHeaders.value,
//     () => allHeaders()
// );

// <template #customHeaderButton>
// <JetDropdown
//     :align="direction"
//     width="36"
//     open="true"
//     contentClasses="py-2 rtl:bg-gradient-to-r ltr:bg-gradient-to-l from-orange-600 to-gray-900"
// >
//     <template #trigger>
//         <span class="inline-flex rounded-md">
//             <Button type="button" color="gradient_orange">
//                 {{ $t("general.show_headers") }}</Button
//             >
//         </span>
//     </template>

//     <template #content>
//         <!-- Account Management -->
//         <div
//             class="flex justify-center px-4 py-1 text-xs text-gray-200 rtl:bg-gradient-to-r ltr:bg-gradient-to-l from-orange-600 to-gray-900"
//         >
//             {{ $t("general.headers") }}
//         </div>
//         <div class="border-t border-gray-200" />

//         <!-- ////////////////////////// filtered headers ////////////////////////////////////// -->
//         <div
//             class="flex flex-col justify-between rtl:bg-gradient-to-r ltr:bg-gradient-to-l from-orange-400 to-gray-800"
//         >
//             <div
//                 v-for="(header, index) in props.headers"
//                 :key="index"
//             >
//                 <Label
//                     class="mx-1 mt-2 mb-2 rtl:bg-gradient-to-r ltr:bg-gradient-to-l from-yellow-500 via-orange-600 to-red-900 px-2 py-1 rounded shadow-md border border-gray-300"
//                 >
//                     <div class="flex justify-between">
//                         <div>
//                             <h1 class="text-gray-200">
//                                 {{
//                                     $t(
//                                         "general." +
//                                             header.name
//                                     )
//                                 }}
//                             </h1>
//                         </div>
//                         <div>
//                             <input
//                                 class="rounded mx-1 border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
//                                 type="checkbox"
//                                 checked
//                                 :id="header[index]"
//                                 :value="header"
//                                 v-model="filteredHeaders"
//                             />
//                         </div>
//                     </div>
//                 </Label>
//             </div>
//         </div>
//         <!-- //////////////////////////////////////////////////////////////////////////////////////// -->

//         <div class="border-t border-gray-100" />
//     </template>
// </JetDropdown>
// </template>
