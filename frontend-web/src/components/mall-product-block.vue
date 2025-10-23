<template>
    <div class="mall-product-block">       
        <div class="">
            <div class="mall-title flex flex-col mb-3" :id="'section-' + index">
                <h2 class="text-lg sm:text-xl font-bold text-transparent bg-clip-text  bg-gradient-to-r border-l-4  pl-2">{{ props.title }}</h2>
                <p class="text-sm text-gray-500 mt-1 pl-2">{{ props.description }}</p>
            </div>
            <div class="product-list grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                <div v-for="item in props.products" :key="item.id">
                    <productItem :item="item" :index="index" @showDetail="showDetail" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import productItem from '@/components/productItem.vue'
import { defineProps } from 'vue'
import { useRouter } from 'vue-router'
const router = useRouter()

const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    description: {
        type: String,
        default: ''
    },
    index: {
        type: Number,
        required: true
    },
    products: {
        type: Array,
        default: () => []
    }
})

const showDetail = (item) => {
    router.push({
        path: '/product-detail',
        query: { 
            id: item.id,
            index: props.index, 
            title: props.title
        }
    })
}
</script>

<style scoped lang="scss">
.gap-3 {
  gap: 0.75rem;
}

.bg-gradient-to-r {
  background-image: linear-gradient(to right, #3B82F5, #6366f1);
}
.grid-cols-1 {
  grid-template-columns: repeat(1, minmax(0, 1fr));
}
.grid {
  display: grid;
}
.border-gray-700 {
    --tw-border-opacity: 1;
    border-color: rgb(55 65 81 / var(--tw-border-opacity, 1));
}
.border-l-4 {
    border-left-width: 4px;
}

@media (min-width: 640px) {
    .sm\:gap-4 {
    gap: 1rem;
  }
  .sm\:grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
@media (min-width: 768px) {
    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}
@media (min-width: 1024px) {
    .lg\:grid-cols-4 {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}
</style>