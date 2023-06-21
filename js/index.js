import sharedScripts from "./shared/sharedScripts.js";

Vue.createApp({
    data() {
        return {
            url: 'api/getApi.php',
            currentDate: '',
            actualTime: '',
            watsInType: 'Solar',
            watsInSum: 0,
            watsOutSum: 0,
            watsFromExtraBattery: 0,
            deltaDataHourByHour: [],
            batteryPercentActual: 0,
            watsInActual: 0,
            watsOutActual: 0
        };
    },
    methods: {
        async getDataFromDateSum(showLoading = true) {
            if (showLoading) {
                this.loadingScreen('true');
            }
            try {
                let className = 'DataFromSaveHistory';
                let functionName = "getDataFromDateSum";
                let response = await fetch(this.url + "?class=" + className + "&function=" + functionName + '&date=' + this.currentDate);
                let deltaData = await response.json();
                this.watsInSum = deltaData.input;
                this.watsOutSum = deltaData.output;
            } catch (error) {
                console.log(error);
            }
            if (showLoading) {
                this.loadingScreen('false');
            }
        },
        async getDataFromTodayHourByHour(showLoading = true) {
            if (showLoading) {
                this.loadingScreen('true');
            }
            try {
                let className = 'DataFromSaveHistory';
                let functionName = "getDataFromTodayHourByHour";
                let response = await fetch(this.url + "?class=" + className + "&function=" + functionName + '&date=' + this.currentDate);
                this.deltaDataHourByHour = await response.json();
            } catch (error) {
                console.log(error);
            }
            if (showLoading) {
                this.loadingScreen('false');
            }
        },
        async getSumDataFromExtraBattery(showLoading = true) {
            if (showLoading) {
                this.loadingScreen('true');
            }
            try {
                let className = 'DataFromSaveHistory';
                let functionName = "getDataFromExtraBatterySum";
                let response = await fetch(this.url + "?class=" + className + "&function=" + functionName + '&date=' + this.currentDate);
                let extraBattery = await response.json();
                this.watsFromExtraBattery = extraBattery.extraBatterySum;
            } catch (error) {
                console.log(error);
            }
            if (showLoading) {
                this.loadingScreen('false');
            }
        },
        async getActualData(showLoading = true) {
            if (showLoading) {
                this.loadingScreen('true');
            }
            try {
                let className = 'DataFromSaveHistory';
                let functionName = "getActualData";
                let response = await fetch(this.url + "?class=" + className + "&function=" + functionName);
                let deltaData = await response.json();
                this.watsInActual = deltaData.input;
                this.watsOutActual = deltaData.output;
                this.watsInType = deltaData.type;
                this.batteryPercentActual = deltaData.battery;
            } catch (error) {
                console.log(error);
            }
            if (showLoading) {
                this.loadingScreen('false');
            }
        },
        changeDate() {
            let date = document.getElementById('selectedDate').value;
            this.currentDate = date;
            this.getDataFromDateSum();
            this.getDataFromTodayHourByHour();
            this.getSumDataFromExtraBattery();
        },
        loadingScreen: sharedScripts.loadingScreenShared,
        loadingScreenStart: sharedScripts.loadingScreenStartShared,
        getTodayDate: sharedScripts.getTodayDateShared,
        getActualTime: sharedScripts.getActualTimeShared
    },
    mounted() {
        Promise.all([
            this.loadingScreenStart(),
            this.getTodayDate(),
            this.getActualTime(),
            this.getDataFromDateSum(),
            this.getDataFromTodayHourByHour(),
            this.getActualData(),
            this.getSumDataFromExtraBattery(),
            setInterval(() => {
                this.getActualTime();
                this.getActualData(false);
                this.getDataFromDateSum(false);
                this.getDataFromTodayHourByHour(false);
                this.getSumDataFromExtraBattery(false);
            }, 10000)
        ]);
    },
}).mount('#index');