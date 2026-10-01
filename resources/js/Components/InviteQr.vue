<script setup>
import QRCode from 'qrcode';
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    value: { type: String, required: true },
});

const canvas = ref(null);

async function draw() {
    if (!canvas.value) {
        return;
    }

    await QRCode.toCanvas(canvas.value, props.value, { width: 192, margin: 1 });
}

onMounted(draw);
watch(() => props.value, draw);
</script>

<template>
    <canvas ref="canvas" class="mx-auto rounded-2xl bg-white" />
</template>
