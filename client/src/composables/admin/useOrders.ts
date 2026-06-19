import { useMutation, useQueryClient } from '@tanstack/vue-query'
import axiosInstance from '@/api/axiosInstance'
import { toast } from 'vue-sonner'
import type { Order, OrderStatus, OrdersResponse } from '@/types/OrderTypes'

export function useOrders() {
  const queryClient = useQueryClient()

  const fetchOrders = async (params?: {
    page?: number
    per_page?: number
    status?: OrderStatus | ''
    search?: string
  }): Promise<OrdersResponse> => {
    const response = await axiosInstance.get('/admin/orders', { params })
    return response.data
  }

  const { mutate: updateOrderStatus, isPending: isUpdating } = useMutation({
    mutationFn: async ({ uuid, status }: { uuid: string; status: OrderStatus }): Promise<Order> => {
      const response = await axiosInstance.put(`/admin/orders/${uuid}`, { status })
      return response.data.order
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['admin-orders'] })
      toast.success('Order status updated successfully')
    },
    onError: (error: any) => {
      toast.error(error.response?.data?.message || 'Failed to update order status')
    },
  })

  return {
    fetchOrders,
    updateOrderStatus,
    isUpdating,
  }
}
