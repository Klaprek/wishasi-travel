import { useCallback, useEffect, useState } from 'react';
import api from '../lib/api';

export default function useFetch(url, options = {}) {
    const { enabled = true, ...config } = options;
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchData = useCallback(async () => {
        if (!enabled || !url) {
            setLoading(false);
            return;
        }
        setLoading(true);
        setError(null);
        try {
            const response = await api.get(url, config);
            setData(response.data.data ?? response.data);
        } catch (err) {
            setError(err.response?.data ?? err.message);
        } finally {
            setLoading(false);
        }
    }, [url, enabled, JSON.stringify(config)]);

    useEffect(() => {
        fetchData();
    }, [fetchData]);

    return { data, loading, error, refetch: fetchData };
}
