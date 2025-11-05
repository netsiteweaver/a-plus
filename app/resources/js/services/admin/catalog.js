import { api } from '@/services/http';

export const catalogApi = {
    // Brands
    listBrands(params = {}) {
        return api.get('/admin/brands', { params });
    },
    createBrand(payload) {
        return api.post('/admin/brands', payload);
    },
    updateBrand(id, payload) {
        return api.put(`/admin/brands/${id}`, payload);
    },
    deleteBrand(id) {
        return api.delete(`/admin/brands/${id}`);
    },

    // Categories
    listCategories(params = {}) {
        return api.get('/admin/categories', { params });
    },
    getCategory(id) {
        return api.get(`/admin/categories/${id}`);
    },
    createCategory(payload) {
        return api.post('/admin/categories', payload);
    },
    updateCategory(id, payload) {
        return api.put(`/admin/categories/${id}`, payload);
    },
    deleteCategory(id) {
        return api.delete(`/admin/categories/${id}`);
    },

    // Products
    listProducts(params = {}) {
        return api.get('/admin/products', { params });
    },
    getProduct(id, params = {}) {
        return api.get(`/admin/products/${id}`, { params });
    },
    createProduct(payload) {
        return api.post('/admin/products', payload);
    },
    updateProduct(id, payload) {
        return api.put(`/admin/products/${id}`, payload);
    },
    deleteProduct(id) {
        return api.delete(`/admin/products/${id}`);
    },

    // Product Variants
    listVariants(productId) {
        return api.get(`/admin/products/${productId}/variants`);
    },
    createVariant(productId, payload) {
        return api.post(`/admin/products/${productId}/variants`, payload);
    },
    updateVariant(productId, variantId, payload) {
        return api.put(`/admin/products/${productId}/variants/${variantId}`, payload);
    },
    deleteVariant(productId, variantId) {
        return api.delete(`/admin/products/${productId}/variants/${variantId}`);
    },

    // Product Options
    listOptions(productId) {
        return api.get(`/admin/products/${productId}/options`);
    },
    createOption(productId, payload) {
        return api.post(`/admin/products/${productId}/options`, payload);
    },
    updateOption(productId, optionId, payload) {
        return api.put(`/admin/products/${productId}/options/${optionId}`, payload);
    },
    deleteOption(productId, optionId) {
        return api.delete(`/admin/products/${productId}/options/${optionId}`);
    },

    listOptionValues(productId, optionId) {
        return api.get(`/admin/products/${productId}/options/${optionId}/values`);
    },
    createOptionValue(productId, optionId, payload) {
        return api.post(`/admin/products/${productId}/options/${optionId}/values`, payload);
    },
    updateOptionValue(productId, optionId, valueId, payload) {
        return api.put(`/admin/products/${productId}/options/${optionId}/values/${valueId}`, payload);
    },
    deleteOptionValue(productId, optionId, valueId) {
        return api.delete(`/admin/products/${productId}/options/${optionId}/values/${valueId}`);
    },

    // Product Media
    listMedia(productId) {
        return api.get(`/admin/products/${productId}/media`);
    },
    createMedia(productId, payload) {
        return api.post(`/admin/products/${productId}/media`, payload);
    },
    updateMedia(productId, mediaId, payload) {
        return api.put(`/admin/products/${productId}/media/${mediaId}`, payload);
    },
    deleteMedia(productId, mediaId) {
        return api.delete(`/admin/products/${productId}/media/${mediaId}`);
    },

    // Product Attribute Values
    listProductAttributes(productId) {
        return api.get(`/admin/products/${productId}/attributes`);
    },
    upsertProductAttribute(productId, payload) {
        return api.post(`/admin/products/${productId}/attributes`, payload);
    },
    updateProductAttribute(productId, attributeValueId, payload) {
        return api.put(`/admin/products/${productId}/attributes/${attributeValueId}`, payload);
    },
    deleteProductAttribute(productId, attributeValueId) {
        return api.delete(`/admin/products/${productId}/attributes/${attributeValueId}`);
    },

    // Related products
    listRelatedProducts(productId) {
        return api.get(`/admin/products/${productId}/related`);
    },
    createRelatedProduct(productId, payload) {
        return api.post(`/admin/products/${productId}/related`, payload);
    },
    updateRelatedProduct(productId, relationId, payload) {
        return api.put(`/admin/products/${productId}/related/${relationId}`, payload);
    },
    deleteRelatedProduct(productId, relationId) {
        return api.delete(`/admin/products/${productId}/related/${relationId}`);
    },

    // Attributes
    listAttributes(params = {}) {
        return api.get('/admin/attributes', { params });
    },
    getAttribute(id) {
        return api.get(`/admin/attributes/${id}`);
    },
    createAttribute(payload) {
        return api.post('/admin/attributes', payload);
    },
    updateAttribute(id, payload) {
        return api.put(`/admin/attributes/${id}`, payload);
    },
    deleteAttribute(id) {
        return api.delete(`/admin/attributes/${id}`);
    },

    listAttributeValues(attributeId) {
        return api.get(`/admin/attributes/${attributeId}/values`);
    },
    createAttributeValue(attributeId, payload) {
        return api.post(`/admin/attributes/${attributeId}/values`, payload);
    },
    updateAttributeValue(attributeId, valueId, payload) {
        return api.put(`/admin/attributes/${attributeId}/values/${valueId}`, payload);
    },
    deleteAttributeValue(attributeId, valueId) {
        return api.delete(`/admin/attributes/${attributeId}/values/${valueId}`);
    },
};
