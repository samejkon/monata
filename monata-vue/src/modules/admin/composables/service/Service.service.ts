import {ref, reactive, computed} from 'vue';
import type { Service } from '@/modules/admin/stores/model/Service.model';
import {getServices, deleteService} from './Service.component';
import {useRoute, useRouter} from "vue-router";
import { getServiceById, updateService, createService } from './Service.component';
import { ServiceStatus } from '../../stores/enum/Service';

export const useService = () => {
    const route = useRoute();
    const router = useRouter();

    const errors = reactive<Record<string, string[]>>({});

    const services = ref<Service[]>([]);
    const page = ref(1);
    const totalPages = ref(1);
    const currentPage = ref(1);

    const isCreate = computed(() => !route.params.id);
    const formTitle = computed(() => isCreate.value ? 'Create User' : 'Update User');
    const buttonTitle = computed(() => isCreate.value ? 'Create' : 'Update');

    const actionDeleteService = async (id: number) => {
        const confirmDelete = confirm('Are you sure you want to delete this user?');
        if (!confirmDelete) return;

        await deleteService(id);    
        await fetchServices();
    };


    const fetchServices = async () => {
        const queryParams = route.query;
        const pageNum = Number(route.query.page) || 1;

        const params = { ...queryParams,  page: pageNum};

        const response = await getServices(params);

        services.value = response.data;
        totalPages.value = response.totalPages;
        page.value = pageNum;
        currentPage.value = page.value;
    };

    const form = reactive({
        name: '',
        price: undefined,
        status: ServiceStatus.Active,
    });

    const handleCreateSubmit = async () => {
        Object.keys(errors).forEach(key => delete errors[key]);
        console.log(errors);
        try {
            await createService(form);
            router.push('/admin/services');
        } catch (error: any) {
            if (error.response?.status === 422) {
                Object.assign(errors, error.response.data.errors);
            } else {
                console.error(error);
            }
        }
    };

    const loadService = async (id: number) => {
      try {
        const serviceID = Number(route.params.id);
        const service = await getServiceById(serviceID);

        Object.assign(form, {
            name: service.name,
            price: service.price,
            status: service.status
        });
      } catch (e) {
        console.error(e);
      }
    };
    
    const handleUpdateSubmit = async () => {
      Object.keys(errors).forEach(key => delete errors[key]);
      console.log(errors);
      try {
        await updateService(Number(route.params.id), form);
        router.push('/admin/services');
      } catch (error: any) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
        } else {
            console.error(error);
        }
      }
    };

    return {
        services,
        page,
        totalPages,
        currentPage,
        fetchServices,
        actionDeleteService,
        isCreate,
        formTitle,
        buttonTitle,
        form,
        errors,
        loadService,
        handleCreateSubmit,
        handleUpdateSubmit,
    };
};