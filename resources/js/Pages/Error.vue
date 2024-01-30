<template>
    <Head :title="'Error: '+message"></Head>
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div class="max-w-lg w-full mx-auto p-6 lg:p-8">
            <div>
                <div class="text-2xl dark:text-white p-3 bg-red-600/10 rounded-lg my-3">{{message}}</div>
            </div>
        </div>
    </div>
</template>

<script>
import {Head} from "@inertiajs/vue3";

export default {
    name: "Error",
    components: {Head},
    props: {
        message: String,
    },
}
</script>

<style scoped>
</style>
