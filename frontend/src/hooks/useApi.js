import { useState, useEffect, useCallback } from 'react';

export function useApi(apiFn, params = null, immediate = true) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(immediate);
  const [error, setError] = useState(null);

  const execute = useCallback(async (overrideParams) => {
    setLoading(true);
    setError(null);
    try {
      const res = await apiFn(overrideParams ?? params);
      setData(res.data);
      return res.data;
    } catch (err) {
      setError(err.response?.data?.message || 'Terjadi kesalahan');
      throw err;
    } finally {
      setLoading(false);
    }
  }, [apiFn, params]);

  useEffect(() => { if (immediate) execute(); }, []);

  return { data, loading, error, execute, setData };
}
