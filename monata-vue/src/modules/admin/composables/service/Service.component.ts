import axios from "axios";
import { api } from "../../stores/api/api";
import type { Service } from "../../stores/model/Service.model";

export const getServices = async (params: { page?: number }): Promise<{ 
  data: Service[]; 
  totalPages: number;
  }> => {
  const response = await api.get('/admin/services', {
      params: {
          ...params,
          page: params.page
      }
  });

  return {
      data: response.data.data,
      totalPages: response.data.meta.last_page
  };
};

export const getServiceById = async (id: number): Promise<Service> => {
    const response = await api.get(`/admin/services/${id}`);

    return response.data.data;
};

export const createService = async (service: Partial<Service>) => {
  const response = await api.post('/admin/services', service);

  return response.data.data;
};

export const updateService = async (id: number, service: Partial<Service>) => {
  const response = await api.put(`/admin/services/${id}`, service);

  return response.data;
};

export const deleteService = async (id: number): Promise<void> => {
    await api.delete(`/admin/services/${id}`);
};