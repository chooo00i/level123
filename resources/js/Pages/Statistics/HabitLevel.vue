<template>
    <div>
        <h2 class="title-xl">습관 행동 순위</h2>
        <div class="relative w-full flex-grow mt-6">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
        <div>
            <ul class="grid lg:grid-cols-2 md:p-6 pt-3">
                <li v-for="(data, index) in habitLevelRankData" :key="index"
                    class="flex items-center justify-between content-sm mt-2 md:pr-3">
                    <div class="flex items-center">
                        <span class="w-8 font-semibold">{{ data.rank }}</span>
                        <div class="w-2.5 h-2.5 rounded-full mr-3" :class="{
                            'bg-sky-200': data.level === 1,
                            'bg-sky-300': data.level === 2,
                            'bg-sky-400': data.level === 3,
                        }"></div>
                        <span class="w-16 font-medium">Level{{ data.level }}</span>
                        <span class="break-all">{{ data.content }}</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="w-10">{{ data.count }}회</span>
                        <span class="w-10 text-right">{{ data.percentage }}%</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'
import ChartDataLabels from 'chartjs-plugin-datalabels'
import { LEVEL_COLOR_MAP } from '@/Utils/Constant'

const { habitLevelRankData } = defineProps({
    habitLevelRankData: Array
})

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ChartDataLabels)
const xData = computed(() => habitLevelRankData.map(item => item.content))
const yData = computed(() => habitLevelRankData.map(item => item.count))

const backgroundColors = computed(() =>
    habitLevelRankData.map(item =>
        LEVEL_COLOR_MAP[item.level]
    )
)

const chartData = computed(() => ({
    labels: xData.value,
    datasets: [
        {
            label: '',
            backgroundColor: backgroundColors.value,
            borderWidth: 0,
            data: yData.value,
        },
    ],
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        datalabels: {
            color: '#075985',
            font: {
                size: 13,
                weight: 'bold'
            }
        },
        legend: {
            display: false, // 범례 숨기기
        },
        tooltip: {
            enabled: false, // 툴팁 제거
        },
    },
    scales: {
        x: {
            grid: {
                display: false, // 가로 줄 보이기
            },
            border: {
                display: false, // X축 선 숨기기
            },
            ticks: {
                display: false, // X축 라벨 유지
            }
        },
        y: {
            grid: {
                display: false, // 세로 줄 숨기기
            },
            border: {
                display: false, // Y축 선 숨기기
            },
            ticks: {
                display: false, // Y축 라벨 숨기기
            }
        }
    }
}
</script>