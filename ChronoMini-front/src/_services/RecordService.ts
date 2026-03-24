import Axios from '@/_services/CallerService';

export interface Record {
  id?: number;
  kid_id: number;
  user_id?: number;
  drop_hour?: string;
  pick_up_hour?: string;
  date?: string;
  amount_hours?: number;
}

export class RecordService {
  static async getMyRecords() {
    try {
      const response = await Axios.get('/myrecords');
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async startRecording(kidId: number) {
    try {
      const response = await Axios.post(`/${kidId}/record/start`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async stopRecording(kidId: number) {
    try {
      const response = await Axios.post(`/${kidId}/record/stop`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async getRecordById(recordId: number) {
    try {
      const response = await Axios.get(`/myrecord/${recordId}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async getKidRecords(kidId: number) {
    try {
      const response = await Axios.get(`/kid/${kidId}/records`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async addAnnotation(recordId: number, annotation: string) {
    try {
      const response = await Axios.post(`/records/${recordId}/annotation`, { annotation });
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async deleteRecord(recordId: number) {
    try {
      const response = await Axios.delete(`/records/${recordId}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }
  static async startBreak(recordId: number) {
    try {
      const response = await Axios.post(`/timebreaks/start`, {
        record_id: recordId
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async endBreak(recordId: number) {
    try {
      const response = await Axios.post(`/timebreaks/end`, {
        record_id: recordId
      });
      return response.data;
    } catch (error) {
      throw error;
    }
  }

  static async checkActiveBreak(recordId: number) {
    try {
      const response = await Axios.get(`/timebreaks/check/${recordId}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }
}
  

export default RecordService;
